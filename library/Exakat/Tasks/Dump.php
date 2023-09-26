<?php declare(strict_types = 1);
/*
 * Copyright 2012-2022 Damien Seguy - Exakat SAS <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/

namespace Exakat\Tasks;

use Exakat\Analyzer\Analyzer;
use Exakat\Analyzer\Dump\AnalyzerDump;
use Exakat\Config;
use Exakat\Exceptions\MissingGremlin;
use Exakat\Exceptions\NoSuchAnalyzer;
use Exakat\Exceptions\NoSuchProject;
use Exakat\Exceptions\NoSuchRuleset;
use Exakat\Exceptions\ProjectNeeded;
use Exakat\GraphElements;
use Exakat\Log;
use Exakat\Query\Query;
use Exakat\Dump\Dump as DumpDb;
use Exakat\Helpers\Timer;
use Exakat\Query\DSL\InitVariable;

class Dump extends Tasks {
    public const CONCURENCE = self::DUMP;

    public const WAITING_LOOP = 1000;

    private array $files = array();

    protected string $logname = self::LOG_NONE;

    private string $linksDown = '';
    private DumpDb $dump;

    private int $argumentsId     = 0;
    private int $methodCount     = 0;
    private int $propertyCount   = 0;
    private int $constantCount   = 0;
    private int $classConstCount = 0;
    private int $citCount        = 0;

    private array $methodIds     = array();
    private array $classConstIds = array();
    private array $propertyIds   = array();

    public function __construct(bool $subTask = self::IS_NOT_SUBTASK) {
        parent::__construct($subTask);

        $this->log = new Log('dump',
            $this->config->project_dir);

        $this->linksDown = GraphElements::linksAsList();
    }

    public function run(): void {
        if (!file_exists($this->config->project_dir)) {
            throw new NoSuchProject((string) $this->config->project);
        }

        if ($this->config->gremlin === 'NoGremlin') {
            throw new MissingGremlin();
        }

        $projectInGraph = $this->gremlin->query('g.V().hasLabel("Project").values("code")');
        assert($projectInGraph !== null, "No gremlin available!\n");

        if ($projectInGraph === null) {
            throw new NoSuchProject((string) $this->config->project);
        }
        if (!isset($projectInGraph[0])) {
            throw new NoSuchProject((string) $this->config->project);
        }

        $projectInGraph = $projectInGraph[0];

        // @todo : restore baseline

        // move this to .dump.sqlite then rename at the end, or any imtermediate time
        // Mention that some are not yet arrived in the snitch
        $this->addSnitch();

        if ($this->config->update !== true && file_exists($this->config->dump)) {
            unlink($this->config->dump);
        }
        $this->dump = DumpDb::factory($this->config->dump, DumpDb::INIT);

        if ($this->config->collect === true) {
            display('Collecting data');

            $this->collect();
        }

        $this->loadSqlDump();

        $counts = array();
        $datastore = new \Sqlite3($this->config->datastore, \SQLITE3_OPEN_READONLY);
        $datastore->busyTimeout(\SQLITE3_BUSY_TIMEOUT);
        $res = $datastore->query('SELECT * FROM analyzed');
        while ($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $counts[$row['analyzer']] = (int) $row['counts'];
        }
        $this->log->log('count analyzed : ' . count($counts) . "\n");
        $this->log->log('counts ' . implode(', ', $counts) . "\n");
        $datastore->close();
        unset($datastore);

        if (!empty($this->config->project_rulesets)) {
            $ruleset = $this->config->project_rulesets;
            if ($ruleset === array('None')) {
                $rulesets = array();
            } else {
                $rulesets = $this->rulesets->getRulesetsAnalyzers($ruleset);
                if (empty($rulesets)) {
                    $r = $this->rulesets->getSuggestionRuleset($ruleset);
                    if (!empty($r)) {
                        echo 'did you mean : ', implode(', ', str_replace('_', '/', $r)), "\n";
                    }

                    throw new NoSuchRuleset(implode(', ', $ruleset));
                }

                $this->log->log('Processing Ruleset ' . join(', ', $ruleset));
                $missing = $this->processResultsRuleset($ruleset, $counts);
                $this->log->log('expandRulesets');
                $this->expandRulesets();
                $this->log->log('collectHashAnalyzer');
                $this->collectHashAnalyzer();
                $this->log->log('end');

                if ($missing === 0) {
                    $this->storeToDumpArray('themas', array_map(function (string $x) {
                        return array('', $x);
                    }, $ruleset));
                    $rulesets = array();
                }
            }
        } elseif (!empty($this->config->program)) {
            $analyzer = $this->config->program;
            if (is_array($analyzer)) {
                $rulesets = $analyzer;
            } else {
                $rulesets = array($analyzer);
            }

            $rulesets = array_unique($rulesets);
            foreach ($rulesets as $id => $ruleset) {
                if (!$this->rulesets->getClass($ruleset)) {
                    display('No such analyzer as ' . $ruleset . '. Omitting.');
                    unset($rulesets[$id]);
                }
            }

            if (empty($rulesets)) {
                throw new NoSuchAnalyzer(implode(', ', $rulesets), $this->rulesets);
            }

            display('Processing ' . count($rulesets) . ' analyzer' . (count($rulesets) > 1 ? 's' : '') . ' : ' . implode(', ', $rulesets));

            if (count($rulesets) === 1) {
                $analyzer = array_pop($rulesets);
                if (isset($counts[$analyzer])) {
                    $this->processMultipleResults(array($analyzer), $counts);
                    $this->collectHashAnalyzer();
                    $rulesets = array();
                } else {
                    display("$analyzer is not run yet.");
                }
            } else {
                $this->processResultsList($rulesets, $counts);
                $this->expandRulesets();
                $this->collectHashAnalyzer();
            }
        } else {
            $rulesets = array();
        }

        $this->log->log('Still ' . count($rulesets) . " to be processed\n");
        display('Still ' . count($rulesets) . " to be processed\n");

        $this->finish();
    }

    public function finalMark(array $finalMark): void {
        $sqlite = new \Sqlite3($this->config->dump);
        $sqlite->busyTimeout(\SQLITE3_BUSY_TIMEOUT);

        $values = array();
        foreach ($finalMark as $key => $value) {
            $values[] = "(null, '$key', '$value')";
        }

        $sqlite->query('REPLACE INTO hash VALUES ' . implode(', ', $values));
    }

    private function processResultsRuleset(array $ruleset, array $counts = array()): int {
        $this->log->log('Processing ' . join(', ', $ruleset));
        $analyzers = $this->rulesets->getRulesetsAnalyzers($ruleset);

        return $this->processMultipleResults($analyzers, $counts);
    }

    private function processResultsList(array $rulesetList, array $counts = array()): int {
        return $this->processMultipleResults($rulesetList, $counts);
    }

    private function processMultipleResults(array $analyzers, array $counts): int {
        $specials = array('Php/Incompilable',
                          'Composer/UseComposer',
                          'Composer/UseComposerLock',
                          'Composer/Autoload',
                          );
        $diff = array_intersect($specials, $analyzers);
        if (!empty($diff)) {
            $this->dump->removeResults($diff);
            foreach ($diff as $d) {
                $this->processResults($d, $counts[$d] ?? -3);
            }
            $analyzers = array_diff($analyzers, $diff);
        }

        $saved = 0;
        $docs = exakat('docs');
        $severities = array();
        $readCounts = array(array());

        $skipAnalysis      = array();
        $ignore_dirs       = array();
        $ignore_namespaces = array();
        $filters           = array();
        $analyzers = array_filter($analyzers, function (string $s): bool {
            return substr($s, 0, 9) !== 'Complete/' &&
                   (substr($s, 0, 5) !== 'Dump/'     ||
                    $s === 'Dump/CouldBeAConstant')
            ;
        });
        // Remove analysis that are not exported via dump
        foreach ($analyzers as $id => $analyzer) {
            $a = $this->rulesets->getInstance($analyzer);
            if ($a instanceof AnalyzerDump) {
                unset($analyzers[$id]);
                $skipAnalysis[] = $analyzer;
            }

            $filters[] = $a->getFilters();

            if (!empty($this->config->{$analyzer}['ignore_dirs'])) {
                $ignore_dirs[$analyzer] = is_array( $this->config->{$analyzer}['ignore_dirs']) ? $this->config->{$analyzer}['ignore_dirs'] : array($this->config->{$analyzer}['ignore_dirs']);
                foreach ($ignore_dirs[$analyzer] as &$ignore) {
                    if ($ignore[0] === '/') {
                        $ignore = "/$ignore.*/";
                    } else {
                        $ignore = "/.*$ignore.*/";
                    }
                }
                unset($ignore);
            }

            if (isset($this->config->{$analyzer}['ignore_namespaces'])) {
                $ignore_namespaces[$analyzer] ??= array();
                foreach ($this->config->{$analyzer}['ignore_namespaces'] as $ignore) {
                    if ($ignore[0] === '/') {
                        $ignore_namespaces[$analyzer][] = '/' . addslashes($ignore) . '.*/i';
                    } else {
                        $ignore_namespaces[$analyzer][] = '/.*' . addslashes($ignore) . '.*/i';
                    }
                }
            }
        }
        $this->dump->removeResults($analyzers);
        $filters = array_merge(...$filters);

        $chunks = array_chunk($analyzers, 50);
        // Gremlin only accepts chunks of 255 maximum

        $this->log->log('Processing ' . count($chunks) . " of 50\n");

        foreach ($chunks as $id => $chunk) {
            $query = $this->newQuery('processMultipleResults ' . $id);
            $query->atomIs('Analysis', Analyzer::WITHOUT_CONSTANTS)
                  ->is('analyzer', $chunk)
                  ->savePropertyAs('analyzer', 'analyzer')
                  ->outIs('ANALYZED')
                  ->atomIsNot('Noresult')
                  ->initVariable('ligne',        'it.get().value("line")')
                  ->initVariable('fullcode_',    'it.get().label() == "Sequence" ? "/**/" : it.get().value("fullcode") ', InitVariable::TYPE_CODE)
                  ->initVariable('file',         '"None"')
                  ->initVariable('theFunction',  '""')
                  ->initVariable('theClass',     '""')
                  ->initVariable('theNamespace', '""')
            ->raw(<<<GREMLIN
where( __.until( hasLabel("Project") ).repeat( 
    __.in($this->linksDown)
      .sideEffect{ if (it.get().label() in ["Function", "Closure", "Arrowfunction", "Magicmethod", "Method"]) { theFunction = it.get().value("fullcode")} }
      .sideEffect{ if (it.get().label() in ["Class", "Trait", "Interface", "Classanonymous"]) { theClass = it.get().value("fullcode")} }
      .sideEffect{ if (it.get().label() == "Namespace") { theNamespace = it.get().value("fullnspath"); } }
      .sideEffect{ if (it.get().label() == "File") { file = it.get().value("fullcode")} }
       ).fold()
)
GREMLIN
            )
            ->getVariable(array('fullcode_', 'file', 'ligne', 'theNamespace', 'theClass', 'theFunction', 'analyzer'),
                array('fullcode',  'file', 'line' , 'namespace',    'class',    'function',    'analyzer'));
            $query->prepareRawQuery();
            try {
                $this->log->log("Starts gremlin query\n");
                $res = $this->gremlin->query($query->getQuery(), $query->getArguments())->toArray();
                $this->log->log("Ends gremlin query\n");
            } catch (\Throwable $e) {
                $rows = explode(PHP_EOL, $e->getMessage());
                $this->log->log('error while dumping data' . PHP_EOL . $rows[0]);

                continue;
            }

            $this->log->log('Dumping ' . count($res) . ' results');
            $toDump = array();
            foreach ($res as $result) {
                if (empty($result)) {
                    continue;
                }

                foreach ($filters as $filter) {
                    if (!$filter->filterFile($result)) {
                        display ('Skipping ' . $result['file'] . ' (' . $filter::class . ') ' . PHP_EOL);
                        --$counts[$result['analyzer']];
                        continue 2;
                    }
                }

                if (isset($severities[$result['analyzer']])) {
                    $severity = $severities[$result['analyzer']];
                } else {
                    $severity = $docs->getDocs($result['analyzer'])['severity'];
                    $severities[$result['analyzer']] = $severity;
                }

                ++$saved;
                $toDump[] = array($result['fullcode'],
                                  $result['file'],
                                  $result['line'],
                                  $result['namespace'],
                                  $result['class'],
                                  $result['function'],
                                  $result['analyzer'],
                                  $severity,
                                  );
            }

            $readCounts[] = $this->dump->addResults($toDump);
        }
        $readCounts = array_merge(...$readCounts);

        $this->log->log(implode(', ', $analyzers) . "\ndumped results $saved");

        $error = 0;
        $emptyResults = $skipAnalysis;
        foreach ($analyzers as $class) {
            if (!isset($counts[$class]) || $counts[$class] < 0) {
                $emptyResults[] = $class;
                continue;
            }

            if ($counts[$class] === 0 && !isset($readCounts[$class])) {
//            	$this->log->log("No results saved for $class\n");
                display("No results saved for $class\n");
                $emptyResults[] = $class;
            } elseif ($counts[$class] === ($readCounts[$class] ?? 0)) {
//            	$this->log->log("All $counts[$class] results saved for $class\n");
                display("All $counts[$class] results saved for $class\n");
            } else {
                $this->log->log("results dumped versus expected for $class : {$readCounts[$class]}/{$counts[$class]}");
//                assert(($counts[$class] ?? 0) === ($readCounts[$class] ?? 0), "'results were not correctly dumped in $class : $readCounts[$class]/$counts[$class]");
                ++$error;
            }
        }

        $this->dump->addEmptyResults($emptyResults);

        return $error;
    }

    private function processResults(string $class, int $count): void {
        if ($this->config->project->isDefault()) {
            throw new ProjectNeeded();
        }

        $this->log->log( "$class : $count\n");
        // No need to go further
        if ($count <= 0) {
            $this->dump->addEmptyResults(array($class));
            return;
        }

        $analyzer = $this->rulesets->getInstance($class);
        $res = $analyzer->getDump();

        $docs = exakat('docs');
        $severity = $docs->getDocs($class)['severity'];

        $toDump = array();
        foreach ($res as $result) {
            if (empty($result)) {
                continue;
            }

            $toDump[] = array($result['fullcode'],
                              $result['file'],
                              $result['line'],
                              $result['namespace'],
                              $result['class'],
                              $result['function'],
                              $class,
                              $severity,
                              );
        }

        if (empty($toDump)) {
            $this->dump->addEmptyResults(array($class));
            return;
        }

        $saved = $this->dump->addResults($toDump);
        $saved = $saved[$class];

        $this->log->log("$class : dumped " . $saved);

        if ($count === $saved) {
            display("All $saved results saved for $class\n");
        } else {
            assert($count === $saved, "'results were not correctly dumped in $class : $saved/$count");
            display("$saved results saved, $count expected for $class\n");
        }
    }

    private function finish(): void {
        $this->dump->close();
        $this->removeSnitch();
    }

    private function collectHashAnalyzer(): void {
        $tables = array('hashAnalyzer',
                       );
        $this->dump->collectTables($tables);
    }

    private function collectFiles(): void {
        $this->files = $this->dump->fetchTable('files')->toHash('file', 'id');
    }

    private function collectHashCounts(Query $query, string $name): void {
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $index = $result->toArray()[0] ?? array();

        $toDump = array();
        foreach ($index as $number => $count) {
            $toDump[] = array('',
                              $name,
                              $number,
                              $count,
                             );
        }

        if (empty($toDump)) {
            $total = 0;
        } else {
            $total = $this->storeToDumpArray('hashResults', $toDump);
        }

        display( "$name : $total");
    }

    private function collectMissingDefinitions(): void {
        $toDump = array();

        // Function calls
        $functioncallCount    = $this->gremlin->query('g.V().hasLabel("Functioncall").count()')[0];
        $functioncallDynamic  = $this->gremlin->query('g.V().hasLabel("Functioncall").not(has("token", within("T_STRING", "T_NS_SEPARATOR", "T_NAME_FULLY_QUALIFIED", "T_NAME_RELATIVE", "T_NAME_QUALIFIED"))).count()')[0];
        $functioncallPhp      = $this->gremlin->query('g.V().hasLabel("Functioncall").has("isPhp", true).count()')[0];
        $functioncallExt      = $this->gremlin->query('g.V().hasLabel("Functioncall").has("isPhp", true).count()')[0];
        $functioncallStubs    = $this->gremlin->query('g.V().hasLabel("Functioncall").has("isPhp", true).count()')[0];
        $functioncallMissed   = $this->gremlin->query('g.V().hasLabel("Functioncall")
             .has("token", within("T_STRING", "T_NS_SEPARATOR", "T_NAME_FULLY_QUALIFIED", "T_NAME_RELATIVE", "T_NAME_QUALIFIED"))
             .not(where(__.in("DEFINITION")))
             .not( __.has("isPhp", true))
             .not( __.has("isExt", true))
             .not( __.has("isStub", true))
             .where( __.out("NAME").hasLabel("Identifier", "Nsname", "Name"))
             .values("fullcode")
        ');
        if ($functioncallMissed->count() === 0) {
            file_put_contents("{$this->config->log_dir}/functions.missing.txt", 'Nothing found');
            $functioncallMissed = 0;
        } else {
            file_put_contents("{$this->config->log_dir}/functions.missing.txt", implode(PHP_EOL, $functioncallMissed->toArray()));
            $functioncallMissed = $functioncallMissed->toInt();
        }

        $stats = array('functioncall total'   => 'functioncallCount',
                       'functioncall missed'  => 'functioncallMissed',
                       'functioncall php'     => 'functioncallPhp',
                       'functioncall ext'     => 'functioncallExt',
                       'functioncall stub'    => 'functioncallStubs',
                       'functioncall dynamic' => 'functioncallDynamic',
                      );

        foreach ($stats as $name => $countVar) {
            $toDump[] = array('',
                              $name,
                              $$countVar,
                              );
        }

        // Normal methods
        $methodCount    = $this->gremlin->query('g.V().hasLabel("Methodcall").count()')[0];
        $methodDynamic  = $this->gremlin->query('g.V().hasLabel("Methodcall").not(__.out("METHOD").has("token", within("T_STRING"))).count()')[0];
        $methodPhp      = $this->gremlin->query('g.V().hasLabel("Methodcall").has("isPhp", true).count()')[0];
        $methodExt      = $this->gremlin->query('g.V().hasLabel("Methodcall").has("isExt", true).count()')[0];
        $methodStubs    = $this->gremlin->query('g.V().hasLabel("Methodcall").has("isStub", true).count()')[0];
        $methodMissed   = $this->gremlin->query('g.V().hasLabel("Methodcall")
             .not(where(__.in("DEFINITION")))
             .where( __.out("METHOD").has("token", "T_STRING"))
             .values("fullcode")
        ');
        if ($methodMissed->count() === 0) {
            file_put_contents("{$this->config->log_dir}/methodcall.missing.txt", 'Nothing found');
            $methodMissed = 0;
        } else {
            file_put_contents("{$this->config->log_dir}/methodcall.missing.txt", implode(PHP_EOL, $methodMissed->toArray()));
            $methodMissed = $methodMissed->toInt();
        }

        $stats = array('methodcall total'   => 'methodCount',
                       'methodcall missed'  => 'methodMissed',
                       'methodcall php'     => 'methodPhp',
                       'methodcall ext'     => 'methodExt',
                       'methodcall stub'    => 'methodStubs',
                       'methodcall dynamic' => 'methodDynamic',
                      );

        foreach ($stats as $name => $countVar) {
            $toDump[] = array('',
                              $name,
                              $$countVar,
                              );
        }

        // Normal member
        $memberCount  = $this->gremlin->query('g.V().hasLabel("Member").count()')[0];
        $memberMissed = $this->gremlin->query('g.V().hasLabel("Member")
             .not(where(__.in("DEFINITION")))
             .where( __.out("MEMBER").has("token", "T_STRING"))
             .values("fullcode")
        ');
        if ($memberMissed->count() === 0) {
            file_put_contents("{$this->config->log_dir}/members.missing.txt", 'Nothing found');
            $memberMissed = 0;
        } else {
            file_put_contents("{$this->config->log_dir}/members.missing.txt", implode(PHP_EOL, $memberMissed->toArray()));
            $memberMissed = $memberMissed->toInt();
        }
        $toDump[] = array('',
                          'member total',
                          $memberCount,
                          );
        $toDump[] = array('',
                          'member missed',
                          $memberMissed,
                          );

        // Static class
        $staticClassCount  = $this->gremlin->query('g.V().hasLabel("Staticclass").count()')[0];
        $staticClassMissed = $this->gremlin->query('g.V().hasLabel("Staticclass")
             .not(where(__.in("DEFINITION")))
             .where( __.out("CLASS").hasLabel("Identifier", "Nsname", "Self", "Parent"))
             .not( __.has("isPhp", true))
             .not( __.has("isExt", true))
             .not( __.has("isStub", true))
             .values("fullcode")
        ');
        if ($staticClassMissed->count() === 0) {
            file_put_contents("{$this->config->log_dir}/staticclass.missing.txt", 'Nothing found');
            $staticClassMissed = 0;
        } else {
            file_put_contents("{$this->config->log_dir}/staticclass.missing.txt", implode(PHP_EOL, $staticClassMissed->toArray() ));
            $staticClassMissed = $staticClassMissed->count();
        }
        $toDump[] = array('',
                          'static class total',
                          $staticClassCount,
                          );
        $toDump[] = array('',
                          'static class missed',
                          $staticClassMissed,
                          );

        // Static methods
        $staticMethodCount    = $this->gremlin->query('g.V().hasLabel("Staticmethodcall").count()')[0];
        $staticMethodDynamic  = $this->gremlin->query('g.V().hasLabel("Staticmethodcall").not(__.out("METHOD").has("token", within("T_STRING"))).count()')[0];
        $staticMethodPhp      = $this->gremlin->query('g.V().hasLabel("Staticmethodcall").has("isPhp", true).count()')[0];
        $staticMethodExt      = $this->gremlin->query('g.V().hasLabel("Staticmethodcall").has("isExt", true).count()')[0];
        $staticMethodStubs    = $this->gremlin->query('g.V().hasLabel("Staticmethodcall").has("isStub", true).count()')[0];
        $staticMethodMissed   = $this->gremlin->query('g.V().hasLabel("Staticmethodcall")
             .not(where(__.in("DEFINITION")))
             .where( __.out("CLASS").hasLabel("Identifier", "Nsname", "Self", "Parent", "Static"))
             .not( __.has("isPhp", true))
             .not( __.has("isExt", true))
             .not( __.has("isStub", true))
             .where( __.out("METHOD").has("token", "T_STRING"))
             .values("fullcode")
        ');
        if ($staticMethodMissed->count() === 0) {
            file_put_contents("{$this->config->log_dir}/staticmethodcall.missing.txt", 'Nothing found');
            $staticMethodMissed = 0;
        } else {
            file_put_contents("{$this->config->log_dir}/staticmethodcall.missing.txt", implode(PHP_EOL, $staticMethodMissed->toArray()));
            $staticMethodMissed = $staticMethodMissed->count();
        }

        $stats = array('static methodcall total'   => 'staticMethodCount',
                       'static methodcall missed'  => 'staticMethodMissed',
                       'static methodcall php'     => 'staticMethodPhp',
                       'static methodcall ext'     => 'staticMethodExt',
                       'static methodcall stub'    => 'staticMethodStubs',
                       'static methodcall dynamic' => 'staticMethodDynamic',
                      );

        foreach ($stats as $name => $countVar) {
            $toDump[] = array('',
                              $name,
                              $$countVar,
                              );
        }

        // Static constants
        $staticConstantCount    = $this->gremlin->query('g.V().hasLabel("Staticonstant").count()')[0];
        $staticConstantDynamic  = $this->gremlin->query('g.V().hasLabel("Staticonstant").not(__.out("METHOD").has("token", within("T_STRING"))).count()')[0];
        $staticConstantPhp      = $this->gremlin->query('g.V().hasLabel("Staticonstant").has("isPhp", true).count()')[0];
        $staticConstantExt      = $this->gremlin->query('g.V().hasLabel("Staticonstant").has("isExt", true).count()')[0];
        $staticConstantStubs    = $this->gremlin->query('g.V().hasLabel("Staticonstant").has("isStub", true).count()')[0];
        $staticConstantMissed   = $this->gremlin->query('g.V().hasLabel("Staticonstant")
             .where(__.out("CLASS").has("token", within("T_STRING", "T_NS_SEPARATOR", "T_NAME_FULLY_QUALIFIED", "T_NAME_RELATIVE", "T_NAME_QUALIFIED")))
             .not(where(__.in("DEFINITION")))
             .not( __.has("isPhp", true))
             .not( __.has("isExt", true))
             .not( __.has("isStub", true))
             .where( __.out("CONSTANT").has("token", "T_STRING"))
             .values("fullcode")
        ');

        if ($staticConstantMissed->count() === 0) {
            file_put_contents("{$this->config->log_dir}/staticconstant.missing.txt", 'Nothing found');
            $staticConstantMissed = 0;
        } else {
            file_put_contents("{$this->config->log_dir}/staticconstant.missing.txt", implode(PHP_EOL, $staticConstantMissed->toArray()));
            $staticConstantMissed = $staticConstantMissed->toInt();
        }

        $stats = array('static constant total'   => 'staticConstantCount',
                       'static constant missed'  => 'staticConstantMissed',
                       'static constant php'     => 'staticConstantPhp',
                       'static constant ext'     => 'staticConstantExt',
                       'static constant stub'    => 'staticConstantStubs',
                       'static constant dynamic' => 'staticConstantDynamic',
                      );

        foreach ($stats as $name => $countVar) {
            $toDump[] = array('',
                              $name,
                              $$countVar,
                              );
        }

        // Static properties
        $staticPropertyCount  = $this->gremlin->query('g.V().hasLabel("Staticproperty").count()')[0];
        $staticPropertyMissed = $this->gremlin->query('g.V().hasLabel("Staticproperty")
             .not(where(__.in("DEFINITION")))
             .not( __.has("isPhp", true))
             .not( __.has("isExt", true))
             .not( __.has("isStub", true))
             .where( __.out("MEMBER").has("token", "T_VARIABLE"))
             .values("fullcode")
        ');
        if ($staticPropertyMissed->count() === 0) {
            file_put_contents("{$this->config->log_dir}/staticproperty.missing.txt", 'Nothing found');
            $staticPropertyMissed = 0;
        } else {
            file_put_contents("{$this->config->log_dir}/staticproperty.missing.txt", implode(PHP_EOL, $staticPropertyMissed->toArray()));
            $staticPropertyMissed = $staticPropertyMissed->toInt();
        }
        $toDump[] = array('',
                          'static property total',
                          $staticPropertyCount,
                          );
        $toDump[] = array('',
                          'static property missed',
                          $staticPropertyMissed,
                          );

        // global constants
        $constantCounts = $this->gremlin->query('g.V().hasLabel("Identifier", "Nsname").count()')[0];
        $constantMissed = $this->gremlin->query('g.V().hasLabel("Identifier", "Nsname")
             .not(has("token", within("T_CONST", "T_FUNCTION")))
             .not(where(__.in("DEFINITION")))
             .not( __.has("isPhp", true))
             .not( __.has("isExt", true))
             .not( __.has("isStub", true))
             .not(where(__.in("NAME").hasLabel("Class", "Defineconstant", "Namespace", "As")))
             .not(where(__.in("EXTENDS", "IMPLEMENTS").hasLabel("Class", "Classanonymous", "Interface")))
             .not(where(__.in().hasLabel("Analysis", "Instanceof", "As", "Staticmethod", "Usetrait", "Usenamespace", "Member", "Constant", "Functioncall", "Methodcallname", "Staticmethodcall", "Staticproperty", "Staticclass", "Staticconstant", "Catch", "Parameter")))
             .values("fullcode")
             ') ?: array();
        if ($constantMissed->count() === 0) {
            file_put_contents("{$this->config->log_dir}/constant.missing.txt", 'Nothing found');
            $constantMissed = 0;
        } else {
            file_put_contents("{$this->config->log_dir}/constant.missing.txt", implode(PHP_EOL, $constantMissed->toArray()));
            $constantMissed = $constantMissed->toInt();
        }
        $toDump[] = array('',
                          'constant total',
                          $constantCounts,
                          );
        $toDump[] = array('',
                          'constant missed',
                          $constantMissed,
                          );

        $this->storeToDumpArray('hash', $toDump);
    }

    public function checkRulesets(string $ruleset, array $analyzers): void {
        $sqliteFile = $this->config->dump;

        $sqlite = new \Sqlite3($sqliteFile);
        $sqlite->busyTimeout(\SQLITE3_BUSY_TIMEOUT);

        $query = 'SELECT analyzer FROM resultsCounts WHERE analyzer IN (' . makeList($analyzers) . ')';
        $ran = array();
        $res = $sqlite->query($query);
        while ($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $ran[] = $row['analyzer'];
        }

        if (empty(array_diff($analyzers, $ran))) {
            $query = "INSERT INTO themas (\"id\", \"thema\") VALUES (null, \"$ruleset\")";
            $sqlite->query($query);
        }
    }

    private function expandRulesets(): void {
        $res = $this->dump->fetchTable('resultsCounts', array('analyzer'));
        $analyzers = $res->toList('analyzer');

        $res = $this->dump->fetchTable('themas', array('thema'));
        $ran = $res->toList('thema');

        $rulesets = $this->rulesets->listAllRulesets();
        $rulesets = array_diff($rulesets, $ran);

        $add = array();
        foreach ($rulesets as $ruleset) {
            $analyzerList = $this->rulesets->getRulesetsAnalyzers(array($ruleset));

            $diff = array_diff($analyzerList, $analyzers);
            $diff = array_filter($diff, function (string $x): bool {
                return (substr($x, 0, 5) !== 'Dump/') && (substr($x, 0, 9) !== 'Complete/');
            });
            if (empty($diff)) {
                $add[] = array('', $ruleset);
            }
        }

        if (!empty($add)) {
            $this->dump->storeInTable('themas', $add);
        }
    }

    private function newQuery(string $title): Query {
        return new Query(0, $this->config->project, $title, $this->config->executable);
    }

    public function collect(): void {
        $this->collectFiles();

        // Dev only
        if ($this->config->is_phar === Config::IS_NOT_PHAR) {
            $timer = new Timer();
            $this->collectMissingDefinitions();
            $timer->end();
            $this->log->log( 'Collected Missing definitions : ' . number_format($timer->duration(Timer::MS), 2) . "ms\n");
        }
    }

    private function storeToDump(string $table, Query $query): int {
        $query->prepareRawQuery();
        if ($query->canSkip()) {
            return 0;
        }
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());

        return $this->dump->storeInTable($table, $result);
    }

    private function storeToDumpArray(string $table, array $result): int {
        return $this->dump->storeInTable($table, $result);
    }

    private function loadSqlDump(): void {
        $dumps = glob($this->config->tmp_dir . '/dump-*.php');
        display('Loading ' . count($dumps) . ' dumped SQL files');

        foreach ($dumps as $dump) {
            include $dump;

            $this->dump->storeQueries($queries);
            unlink($dump);
        }
    }
}

?>