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
use Exakat\GraphElements;
use Exakat\Log;
use Exakat\Query\Query;
use Exakat\Dump\Dump as DumpDb;
use Exakat\Helpers\Timer;

class Dump extends Tasks {
    public const CONCURENCE = self::DUMP;

    private $files = array();

    protected $logname = self::LOG_NONE;

    private $linksDown = '';
    private $dump      = null;

    public const WAITING_LOOP = 1000;

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
            throw new NoSuchProject($this->config->project);
        }

        if ($this->config->gremlin === 'NoGremlin') {
            throw new MissingGremlin();
        }

        $projectInGraph = $this->gremlin->query('g.V().hasLabel("Project").values("code")');
        if (empty($projectInGraph)) {
            throw new NoSuchProject($this->config->project);
        }
        if (!isset($projectInGraph[0])) {
            throw new NoSuchProject($this->config->project);
        }

        $projectInGraph = $projectInGraph[0];

// TODO
//        $this->sqliteFilePrevious = $this->config->dump_previous;
// also baseline

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
        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
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
                $missing = $this->processResultsRuleset($ruleset, $counts);
                $this->expandRulesets();
                $this->collectHashAnalyzer();

                if ($missing === 0) {
                    $this->storeToDumpArray('themas', array_map(function (string $x) { return array('', $x); }, $ruleset));
                    $rulesets = array();
                }
            }

        } elseif (!empty($this->config->program)) {
            $analyzer = $this->config->program;
            if(is_array($analyzer)) {
                $rulesets = $analyzer;
            } else {
                $rulesets = array($analyzer);
            }

            $rulesets = array_unique($rulesets);
            foreach($rulesets as $id => $ruleset) {
                if (!$this->rulesets->getClass($ruleset)) {
                    display('No such analyzer as ' . $ruleset . '. Omitting.');
                    unset($rulesets[$id]);
                }
            }

            if (empty($rulesets)) {
                throw new NoSuchAnalyzer($rulesets, $this->rulesets);
            }

            display('Processing ' . count($rulesets) . ' analyzer' . (count($rulesets) > 1 ? 's' : '') . ' : ' . implode(', ', $rulesets));

            if(count($rulesets) > 1) {
                $this->processResultsList($rulesets, $counts);
                $this->expandRulesets();
                $this->collectHashAnalyzer();
            } else {
                $analyzer = array_pop($rulesets);
                if (isset($counts[$analyzer])) {
                    $this->processMultipleResults(array($analyzer), $counts);
                    $this->collectHashAnalyzer();
                    $rulesets = array();
                } else {
                    display("$analyzer is not run yet.");
                }
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
        foreach($finalMark as $key => $value) {
            $values[] = "(null, '$key', '$value')";
        }

        $sqlite->query('REPLACE INTO hash VALUES ' . implode(', ', $values));
    }

    private function processResultsRuleset(array $ruleset, array $counts = array()): int {
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
            foreach($diff as $d) {
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
        foreach($analyzers as $id => $analyzer) {
            $a = $this->rulesets->getInstance($analyzer);
            if ($a instanceof AnalyzerDump) {
                unset($analyzers[$id]);
                $skipAnalysis[] = $analyzer;
            }

            $filters[] = $a->getFilters();

            if (!empty($this->config->{$analyzer}['ignore_dirs'])) {
                $ignore_dirs[$analyzer] = is_array( $this->config->{$analyzer}['ignore_dirs']) ? $this->config->{$analyzer}['ignore_dirs'] : array($this->config->{$analyzer}['ignore_dirs']);
                foreach($ignore_dirs[$analyzer] as &$ignore) {
                    if ($ignore[0] === '/') {
                        $ignore = "/$ignore.*/";
                    } else {
                        $ignore = "/.*$ignore.*/";
                    }
                }
                unset($ignore);
            }

            if (isset($this->config->{$analyzer}['ignore_namespaces'])) {
                $ignore_namespaces[$analyzer] = $ignore_namespaces[$analyzer] ?? array();
                foreach($this->config->{$analyzer}['ignore_namespaces'] as $ignore) {
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

        $chunks = array_chunk($analyzers, 100);
        // Gremlin only accepts chunks of 255 maximum

        foreach($chunks as $id => $chunk) {
            $query = $this->newQuery('processMultipleResults ' . $id);
            $query->atomIs('Analysis', Analyzer::WITHOUT_CONSTANTS)
                  ->is('analyzer', $chunk)
                  ->savePropertyAs('analyzer', 'analyzer')
                  ->outIs('ANALYZED')
                  ->atomIsNot('Noresult')
                  ->initVariable('ligne',        'it.get().value("line")')
                  ->initVariable('fullcode_',    'it.get().value("fullcode")')
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
                $res = $this->gremlin->query($query->getQuery(), $query->getArguments())->toArray();
            } catch (\Throwable $e) {
                $rows = explode(PHP_EOL, $e->getMessage());
                $this->log->log('error while dumping data' . PHP_EOL . $rows[0]);

                continue;
            }

            $toDump = array();
            foreach($res as $result) {
                if (empty($result)) {
                    continue;
                }

                foreach($filters as $filter) {
                    if (!$filter->filterFile($result)) {
                        display ('Skipping ' . $result['file'] . ' (' . get_class($filter) . ') ' . PHP_EOL);
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

        $this->log->log(implode(', ', $analyzers) . " : dumped $saved");

        $error = 0;
        $emptyResults = $skipAnalysis;
        foreach($analyzers as $class) {
            if (!isset($counts[$class]) || $counts[$class] < 0) {
                $emptyResults[] = $class;
                continue;
            }

            if ($counts[$class] === 0 && !isset($readCounts[$class])) {
                display("No results saved for $class\n");
                $emptyResults[] = $class;
            } elseif ($counts[$class] === ($readCounts[$class] ?? 0)) {
                display("All $counts[$class] results saved for $class\n");
            } else {
                assert(($counts[$class] ?? 0) === ($readCounts[$class] ?? 0), "'results were not correctly dumped in $class : $readCounts[$class]/$counts[$class]");
                ++$error;
            }
        }

        $this->dump->addEmptyResults($emptyResults);

        return $error;
    }

    private function processResults(string $class, int $count): void {
        $this->log->log( "$class : $count\n");
        // No need to go further
        if ($count <= 0) {
            $saved = $this->dump->addEmptyResults(array($class));
            return;
        }

        $analyzer = $this->rulesets->getInstance($class, $this->gremlin, $this->config);
        $res = $analyzer->getDump();

        $docs = exakat('docs');
        $severity = $docs->getDocs($class)['severity'];

        $toDump = array();
        foreach($res as $result) {
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

    private function collectStructures(): void {

        $this->dump->cleanTable('phpdoc');
        $this->dump->cleanTable('attributes');
        $this->dump->cleanTable('typehints');

        $namespacesId = $this->collectStructures_namespaces();

        $MAX_LOOPING = Analyzer::MAX_LOOPING;
        $query = $this->newQuery('cit classes');
        $query->atomIs('Class', Analyzer::WITHOUT_CONSTANTS)

              ->initVariable('extendList',     '""')
              ->initVariable('implementList',  '[]')
              ->initVariable('useList',        '[]')
              ->initVariable('usesOptions',    '[]')
              ->initVariable('lines',          '[it.get().value("line")]')
              ->initVariable('file',           "''")

              ->collectOut('phpdoc',     'PHPDOC')
              ->collectOut('attributes', 'ATTRIBUTE')

              ->raw(<<<GREMLIN
 where(__.out("EXTENDS").optional(__.out("DEFINITION").where(__.in("USE"))).sideEffect{ extendList = it.get().value("fullnspath"); }.fold() )
.where(__.out("IMPLEMENTS").optional(__.out("DEFINITION").where(__.in("USE"))).sideEffect{ implementList.push( it.get().value("fullnspath"));}.fold() )
.where(__.out("USE").hasLabel("Usetrait").out("USE").optional(__.out("DEFINITION").where(__.in("USE"))).sideEffect{ useList.push( it.get().value("fullnspath"));}.fold() )
.where(__.out("USE").hasLabel("Usetrait").out("BLOCK").out("EXPRESSION").sideEffect{ usesOptions.push( it.get().value("fullcode"));}.fold() )
.where(__.out("METHOD", "MAGICMETHOD", "USE", "PPP", "CONST").emit().repeat( __.out($this->linksDown)).times($MAX_LOOPING).has("line").sideEffect{ lines.add(it.get().value("line")); }.fold())
.where(__.in().emit().repeat( __.inE().not(hasLabel("DEFINITION")).outV() ).until(hasLabel("File")).hasLabel("File").sideEffect{ file = it.get().value("fullcode"); }.fold() )
GREMLIN
             )->selectMap(array(
         'id'             => '""',
         'fullnspath'     => 'it.get().value("fullnspath")',
         'name'           => 'it.get().vertices(OUT, "NAME").next().value("fullcode")',
         'namespace'      => '1',
         'type'           => '"class"',
         'abstract'       => 'it.get().properties("abstract").any()',
         'final'          => 'it.get().properties("final").any()',
         'phpdoc'         => 'phpdoc',
         'begin'          => 'lines.min()',
         'end'            => 'lines.max()',
         'file'           => 'file',
         'line'           => 'it.get().value("line")',
         'extends'        => 'extendList',
         'implements'     => 'implementList',
         'uses'           => 'useList.unique()',
         'usesOptions'    => 'usesOptions.join(";")',
         'attributes'     => 'attributes',
             ));

        $query->prepareRawQuery();
        $classes = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $total = 0;

        $cit            = array();
        $toAttributes   = array();
        $toPhpdoc       = array();
        $citId          = array();
        $cit_implements = array();
        $cit_extends    = array();
        $cit_use        = array();

        foreach($classes as $row) {
            $namespace = preg_replace('#\\\\[^\\\\]*?$#is', '', $row['fullnspath']) . '\\';

            if (isset($namespacesId[$namespace])) {
                $namespaceId = $namespacesId[$namespace];
            } else {
                $namespaceId = 1;
            }

            $cit_implements[$row['line'] . $row['fullnspath']] = $row['implements'];
            unset($row['implements']);
            $cit_use[$row['line'] . $row['fullnspath']] = array('uses'    => $row['uses'],
                                                                'options' => $row['usesOptions'],
                                                                );

            if (!empty($row['attributes'])) {
                foreach($row['attributes'] as $attribute) {
                    $toAttributes[] = array(0,
                                            'class',
                                            $this->citCount,
                                            $attribute);
                }
            }

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'class',
                                        $this->citCount,
                                        $phpdoc
                                        );
                }
            }

            unset($row['uses']);
            unset($row['usesOptions']);
            unset($row['attributes']);
            unset($row['phpdoc']);
            $citId[$row['line'] . $row['fullnspath']] = ++$this->citCount;
            unset($row['fullnspath']);
            $row['namespace'] = $namespaceId;
            $cit[] = $row;

            ++$total;
        }
        display("$total classes\n");

        // Interfaces
        $query = $this->newQuery('cit interfaces');
        $query->atomIs('Interface', Analyzer::WITHOUT_CONSTANTS)
              ->initVariable('extendList',  '[]')
              ->initVariable('lines',       '[it.get().value("line")]')
              ->initVariable('file',        '""')

              ->collectOut('phpdoc',     'PHPDOC')

              ->raw(<<<GREMLIN
 where(__.out("EXTENDS").sideEffect{ extendList.add(it.get().value("fullnspath")); }.fold() )
.where( __.out("METHOD", "MAGICMETHOD", "CONST").emit().repeat( __.out($this->linksDown)).times($MAX_LOOPING).has("line").sideEffect{ lines.add(it.get().value("line")); }.fold())
.where( __.in().emit().repeat( __.inE().not(hasLabel("DEFINITION")).outV()).until(hasLabel("File")).hasLabel("File").sideEffect{ file = it.get().value("fullcode"); }.fold() )
GREMLIN
              )
              ->selectMap(array(
         'id'         => '""',
         'fullnspath' => 'it.get().value("fullnspath")',
         'name'       => 'it.get().vertices(OUT, "NAME").next().value("fullcode")',
         'namespace'  => '1',
         'type'       => '"interface"',
         'abstract'   => '0',
         'final'      => '0',
         'phpdoc'     => 'phpdoc',
         'begin'      => 'lines.min()',
         'end'        => 'lines.max()',
         'file'       => 'file',
         'line'       => 'it.get().value("line")',
         'extending'  => 'extendList',
              ));

        $query->prepareRawQuery();
        $interfaces = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $total      = 0;
        foreach($interfaces as $row) {
            $namespace = preg_replace('#\\\\[^\\\\]*?$#is', '', $row['fullnspath']) . '\\';

            if (isset($namespacesId[$namespace])) {
                $namespaceId = $namespacesId[$namespace];
            } else {
                $namespaceId = 1;
            }

            $cit_extends[$row['line'] . $row['fullnspath']] = $row['extending'];
            unset($row['extending']);

            $citId[$row['line'] . $row['fullnspath']] = ++$this->citCount;
            $row['namespace'] = $namespaceId;
            $row['extends'] = '';

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'interface',
                                        $this->citCount,
                                        $phpdoc
                                        );
                }
            }

            unset($row['fullnspath']);
            unset($row['phpdoc']);

            $cit[] = $row;

            ++$total;
        }
        display("$total interfaces\n");

        // Enumerations
        $query = $this->newQuery('cit enums');
        $query->atomIs('Enum', Analyzer::WITHOUT_CONSTANTS)
              ->initVariable('lines',       '[it.get().value("line")]')
              ->initVariable('file',        '""')
              ->initVariable('type',        '""')

              ->collectOut('phpdoc',     'PHPDOC')
              ->collectOut('attributes', 'ATTRIBUTE')

              ->raw(<<<GREMLIN
 where(__.out("EXTENDS").sideEffect{ extendList.add(it.get().value("fullnspath")); }.fold() )
.where( __.out("METHOD", "MAGICMETHOD", "CONST").emit().repeat( __.out($this->linksDown)).times($MAX_LOOPING).has("line").sideEffect{ lines.add(it.get().value("line")); }.fold())
.where( __.in().emit().repeat( __.inE().not(hasLabel("DEFINITION")).outV()).until(hasLabel("File")).hasLabel("File").sideEffect{ file = it.get().value("fullcode"); }.fold() )
.where(__.out("TYPEHINT").sideEffect{ type = it.get().value("fullcode"); }.fold() )
GREMLIN
             )
             ->selectMap(array(
         'id'         => '""',
         'fullnspath' => 'it.get().value("fullnspath")',
         'name'       => 'it.get().vertices(OUT, "NAME").next().value("fullcode")',
         'namespace'  => '1',
         'type'       => '"enum"',
         'abstract'   => '0',
         'final'      => '0',
         'phpdoc'     => 'phpdoc',
         'begin'      => 'lines.min()',
         'end'        => 'lines.max()',
         'file'       => 'file',
         'line'       => 'it.get().value("line")',
         'extends'    => 'type',
         'attributes' => 'attributes',
             ));

        $query->prepareRawQuery();
        $enumerations = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $total = 0;
        $toAttributes   = array();
        foreach($enumerations as $row) {
            $namespace = preg_replace('#\\\\[^\\\\]*?$#is', '', $row['fullnspath']) . '\\';

            if (isset($namespacesId[$namespace])) {
                $namespaceId = $namespacesId[$namespace];
            } else {
                $namespaceId = 1;
            }

            if (!empty($row['attributes'])) {
                foreach($row['attributes'] as $attribute) {
                    $toAttributes[] = array(0,
                                            'enum',
                                            $this->citCount,
                                            $attribute);
                }
            }

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'enum',
                                        $this->citCount,
                                        $phpdoc
                                        );
                }
            }

            $citId[$row['line'] . $row['fullnspath']] = ++$this->citCount;
            unset($row['fullnspath']);
            unset($row['attributes']);
            unset($row['phpdoc']);
            $row['namespace'] = $namespaceId;

            $cit[] = $row;

            ++$total;
        }
        display("$total enums\n");

        // Traits
        $query = $this->newQuery('cit traits');
        $query->atomIs('Trait', Analyzer::WITHOUT_CONSTANTS)
              ->initVariable('useList',      '[]')
              ->initVariable('usesOptions',  '[]')
              ->initVariable('lines',        '[it.get().value("line")]')
              ->initVariable('file',         '""')

              ->collectOut('phpdoc',     'PHPDOC')

              ->raw(<<<GREMLIN
 where(__.out("USE").hasLabel("Usetrait").out("USE").sideEffect{ useList.push( it.get().value("fullnspath"));}.fold() )
.where(__.out("USE").hasLabel("Usetrait").out("BLOCK").out("EXPRESSION").sideEffect{ usesOptions.push( it.get().value("fullcode"));}.fold() )
.where( __.out("METHOD", "MAGICMETHOD", "CONST", "USE", "PPP").emit().repeat( __.out($this->linksDown)).times($MAX_LOOPING).has("line").sideEffect{ lines.add(it.get().value("line")); }.fold())
.where( __.in().emit().repeat( __.inE().not(hasLabel("DEFINITION")).outV()).until(hasLabel("File")).hasLabel("File").sideEffect{ file = it.get().value("fullcode"); }.fold() )
GREMLIN
)           ->selectMap(array(
         'id'          => '""',
         'fullnspath'  => 'it.get().value("fullnspath")',
         'name'        => 'it.get().vertices(OUT, "NAME").next().value("fullcode")',
         'namespace'   => '1',
         'type'        => '"trait"',
         'abstract'    => '0',
         'final'       => '0',
         'phpdoc'      => 'phpdoc',
         'begin'       => 'lines.min()',
         'end'         => 'lines.max()',
         'file'        => 'file',
         'line'        => 'it.get().value("line")',
         'extends'     => '""',
         'implements'  => '[]',
         'uses'        => 'useList.unique()',
         'usesOptions' => 'usesOptions.join(";")',
));


        $query->prepareRawQuery();
        $traits = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $total = 0;
        foreach($traits as $row) {
            $namespace = preg_replace('#\\\\[^\\\\]*?$#is', '', $row['fullnspath']) . '\\';

            if (isset($namespacesId[$namespace])) {
                $namespaceId = $namespacesId[$namespace];
            } else {
                $namespaceId = 1;
            }

            $row['implements'] = array(); // always empty

            unset($row['implements']);
            $cit_use[$row['line'] . $row['fullnspath']] = array('uses'    => $row['uses'],
                                                                'options' => $row['usesOptions'],
                                                                );

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'trait',
                                        $this->citCount,
                                        $phpdoc
                                        );
                }
            }

            unset($row['uses']);
            unset($row['usesOptions']);
            unset($row['phpdoc']);
            $citId[$row['line'] . $row['fullnspath']] = ++$this->citCount;
            unset($row['fullnspath']);
            $row['namespace'] = $namespaceId;

            $cit[] = $row;

            ++$total;
        }
        display("$total traits\n");

        if (!empty($cit)) {
            foreach($cit as &$aCit) {
                if (empty($aCit['extends'])) {
                    continue;
                }

                $citIds = preg_grep('/^\d+' . preg_quote($aCit['extends'], '/') . '$/i', array_keys($citId));

                if (!empty($citIds)) {
                    $aCit['extends'] = (int) $citId[array_pop($citIds)];
                }
            }
            unset($aCit);
            $this->storeToDumpArray('cit', $cit);

            $toDump = array();
            foreach($cit_implements as $id => $impl) {
                foreach($impl as $implements) {
                    $citIds = preg_grep('/^\d+' . preg_quote(mb_strtolower($implements)) . '$/', array_keys($citId));

                    if (empty($citIds)) {
                        $toDump[] = array('', $citId[$id], $implements, 'implements', '');
                    } else {
                        // Here, we are missing the one that are not found
                        foreach($citIds as $c) {
                            $toDump[] = array('', $citId[$id], $citId[$c], 'implements', '');
                        }
                    }
                }
            }

            $total = $this->storeToDumpArray('cit_implements', $toDump);
            display("$total implements \n");

            $toDump = array();
            foreach($cit_use as $id => $use1) {
                $options = $use1['options'];

                foreach($use1['uses'] as $uses) {
                    $citIds = preg_grep('/^\d+\\\\' . addslashes(mb_strtolower($uses)) . '$/', array_keys($citId));

                    if (empty($citIds)) {
                        $toDump[] = array('',
                                          $citId[$id],
                                          $uses,
                                          'use',
                                          $options,
                                          );
                    } else {
                        // Here, we are missing the one that are not found
                        foreach($citIds as $c) {
                            $toDump[] = array('',
                                              $citId[$id],
                                              $uses,
                                              'use',
                                              $options,
                                              );
                        }
                    }

                    // Options are stored only one for all. PHP doesn't care.
                    $options = '';
                }
            }
            $total = $this->storeToDumpArray('cit_implements', $toDump);
            display("$total uses\n");

            $toDump = array();
            foreach($cit_extends as $id => $extend) {
                foreach($extend as $extends) {
                    $citIds = preg_grep('/^\d+' . preg_quote(mb_strtolower($extends)) . '$/', array_keys($citId));

                    if (empty($citIds)) {
                        $toDump[] = array('',
                                          $citId[$id],
                                          $extends,
                                          'extends',
                                          '',
                                          );
                    } else {
                        // Here, we are missing the one that are not found
                        foreach($citIds as $c) {
                            $toDump[] = array('',
                                              $citId[$id],
                                              $citId[$c],
                                              'extends',
                                              '',
                                              );
                        }
                    }
                }
            }
            $total = $this->storeToDumpArray('cit_implements', $toDump);
            display("$total extends\n");

            $this->storeToDumpArray('attributes', $toAttributes);
            $this->storeToDumpArray('phpdoc'    , $toPhpdoc);
        }

        // Methods
        $query = $this->newQuery('cit methods');
        $query->atomIs(array('Method', 'Magicmethod'), Analyzer::WITHOUT_CONSTANTS)
              ->raw(<<<'GREMLIN'
     coalesce( 
            __.out("BLOCK").out("EXPRESSION").hasLabel("As"),
            __.hasLabel("Method", "Magicmethod")
     )
GREMLIN
)
             ->initVariable('returntype',       '[]')
             ->initVariable('returntype_fnp',   '[]')
             ->initVariable('returntype_type',  'it.get().value("typehint")')

              ->collectOut('phpdoc',     'PHPDOC')
              ->collectOut('attributes', 'ATTRIBUTE')

             ->raw(<<<GREMLIN
      where(
        __.coalesce( 
            __.out("AS").sideEffect{alias = it.get().value("fullcode")}.in("AS")
              .out("NAME").in("DEFINITION").hasLabel("Method", "Magicmethod"), 
            __.sideEffect{ alias = false; }
          )
         .as("method")
         .in("METHOD", "MAGICMETHOD").hasLabel("Class", "Interface", "Trait").has("line").sideEffect{classe = it.get().value("fullnspath"); classline =  it.get().value("line"); }
         .select("method")
         .where( __.sideEffect{ lines = [it.get().value("line")];}
                   .out("BLOCK").out("EXPRESSION")
                   .emit().repeat( __.out($this->linksDown)).times($MAX_LOOPING)
                   .has("line").sideEffect{ lines.add(it.get().value("line")); }
                   .fold()
          )
          .where( __.out('RETURNTYPE').not(hasLabel('Void')).sideEffect{ returntype.add(it.get().value("fullcode"));  
                                                                         returntype_fnp.add(it.get().value("fullnspath"));}.fold())
          .where( __.out('NAME').sideEffect{ name = it.get().value("fullcode")}.fold())
          .map{ 

    if (alias == false) {
        signature = it.get().value("fullcode");
    } else {
        signature = it.get().value("fullcode").replaceFirst("function .*?\\\\(", "function "+alias+"(" );
    }
        }
      ) 
GREMLIN
)
            ->selectMap(array(
         'signature'       => 'signature',
         'name'            => 'name',
         'abstract'        => 'it.get().properties("abstract").any()',
         'final'           => 'it.get().properties("final").any()',
         'static'          => 'it.get().properties("static").any()',
         'reference'       => 'it.get().properties("reference").any()',
         'returntype'      => 'returntype',
         'returntype_fnp'  => 'returntype_fnp',
         'returntype_type' => 'returntype_type',
         'visibility'      => 'it.get().value("visibility")',
         'class'           => 'classe',
         'phpdoc'          => 'phpdoc',
         'begin'           => 'lines.min()',
         'end'             => 'lines.max()',
         'classline'       => 'classline',
         'attributes'      => 'attributes',
            ));

        $query->prepareRawQuery();
        $methods = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $toDump        = array();
        $toAttributes  = array();
        $toTypehints   = array();
        $toPhpdoc      = array();
        foreach($methods as $row) {
            $row['visibility'] = $row['visibility'] === 'none' ? '' : $row['visibility'];

            if (!isset($citId[$row['classline'] . $row['class']])) {
                continue;
            }
            $methodId = $row['class'] . '::' . mb_strtolower($row['name']);
            if (isset($this->methodIds[$methodId])) {
                continue; // skip double
            }
            $this->methodIds[$methodId] = ++$this->methodCount;

            assert(isset($citId[$row['classline'] . $row['class']]));

            $toDump[] = array($this->methodCount,
                             $row['name'],
                             $citId[$row['classline'] . $row['class']],
                             (int) $row['static'],
                             (int) $row['final'],
                             (int) $row['abstract'],
                             (int) $row['reference'],
                             $row['visibility'],
                             $row['returntype_type'],
                             (int) $row['begin'],
                             (int) $row['end']
                             );
            ++$total;

            if (!empty($row['attributes'])) {
                foreach($row['attributes'] as $attribute) {
                    $toAttributes[] = array(0,
                                            'method',
                                            $this->methodCount,
                                            $attribute);
                }
            }

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'method',
                                        $this->methodCount,
                                        $phpdoc
                                        );
                }
            }

            if (!empty($row['returntype'])) {
                foreach($row['returntype'] as $id => $typehint) {
                    $toTypehints[] = array(0,
                                           'method',
                                            $this->methodCount,
                                            $typehint,
                                            $row['returntype_fnp'][$id],
                                           );
                }
            }

        }
        $this->dump->cleanTable('methods');
        $this->storeToDumpArray('attributes', $toAttributes);
        $this->storeToDumpArray('typehints',  $toTypehints);
        $this->storeToDumpArray('phpdoc',     $toPhpdoc);
        $total = $this->storeToDumpArray('methods', $toDump);

        // Parameters
        $query = $this->newQuery('Method parameters');
        $query->atomIs('Parameter', Analyzer::WITHOUT_CONSTANTS)
              ->inIs('ARGUMENT')
              ->atomIs(array('Method', 'Magicmethod'), Analyzer::WITHOUT_CONSTANTS)
              ->raw(<<<'GREMLIN'
where( __.out('NAME').sideEffect{ methode = it.get().value("fullcode").toString().toLowerCase() }.fold())
GREMLIN
)
             ->inIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Class', 'Interface', 'Trait'), Analyzer::WITHOUT_CONSTANTS)
             ->savePropertyAs('fullnspath', 'classe')
             ->savePropertyAs('line', 'classline')
             ->back('first')

             ->initVariable('ligne',          'it.get().value("line")')
             ->initVariable('init',           '""')
             ->initVariable('expression',     '0')
             ->initVariable('hasDefault',     '0')
             ->initVariable('typehint_hints', '[]')
             ->initVariable('typehint_fnp',   '[]')
             ->initVariable('typehint_type',  'it.get().value("typehint")')

             ->collectOut('phpdoc',     'PHPDOC')
             ->collectOut('attributes', 'ATTRIBUTE')
             ->collectOut('name',       'NAME'      , 'fullcode')

             ->raw(<<<'GREMLIN'
 where( __.out('TYPEHINT').not(hasLabel('Void')).not(__.in('DEFAULT')).sideEffect{ typehint_hints.add(it.get().value("fullcode")); typehint_fnp.add(it.get().value("fullnspath"));}.fold())
.where( __.out('DEFAULT').not(where(__.in("RIGHT"))).sideEffect{ init = it.get().value("fullcode"); if (it.get().label() in ["Integer", "Null", "Float", "Boolean", "String", "Heredoc"]) { hasDefault = 1; } else { hasDefault = 1; expression = 1; }}.fold())
GREMLIN
)
              ->selectMap(array(
         'name'          => 'name[0]',
         'rank'          => 'it.get().value("rank")',
         'variadic'      => 'it.get().properties("variadic").any()',
         'reference'     => 'it.get().properties("reference").any()',
         'classe'        => 'classe',
         'methode'       => 'methode',
         'line'          => 'ligne',
         'classline'     => 'classline',
         'init'          => 'init',
         'expression'    => 'expression',
         'hasDefault'    => 'hasDefault',
         'typehint'      => 'typehint_hints',
         'typehint_fnp'  => 'typehint_fnp',
         'typehint_type' => 'typehint_type',
         'phpdoc'        => 'phpdoc',
         'attributes'    => 'attributes',
));


        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $toDump       = array();
        $toAttributes = array();
        $toTypehints  = array();
        foreach($result->toArray() as $row) {
            assert(isset($this->methodIds[$row['classe'] . '::' . mb_strtolower($row['methode'])]));
            $toDump[] = array(++$this->argumentsId,
                              $row['name'],
                              (int) $citId[$row['classline'] . $row['classe']],
                              $this->methodIds[$row['classe'] . '::' . mb_strtolower($row['methode'])],
                              (int) $row['rank'],
                              (int) $row['reference'],
                              (int) $row['variadic'],
                              $row['init'],
                              $row['expression'],
                              $row['hasDefault'],
                              (int) $row['line'],
                              $row['typehint_type'],
            );

            if (!empty($row['attributes'])) {
                foreach($row['attributes'] as $attribute) {
                    $toAttributes[] = array(0,
                                            'argument',
                                            $this->argumentsId,
                                            $attribute);
                }
            }

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'argument',
                                        $this->argumentsId,
                                        $phpdoc
                                        );
                }
            }

            if (!empty($row['typehint'])) {
                foreach($row['typehint'] as $id => $typehint) {
                    $toTypehints[] = array(0,
                                           'argument',
                                            $this->argumentsId,
                                            $typehint,
                                            $row['typehint_fnp'][$id],
                                           );
                }
            }
        }

        $total = $this->storeToDumpArray('arguments', $toDump);
        $this->storeToDumpArray('attributes',  $toAttributes);
        $this->storeToDumpArray('typehints',   $toTypehints);
        $this->storeToDumpArray('phpdoc',      $toPhpdoc);

        // Properties
        $query = $this->newQuery('Properties');
        $query->atomIs('Propertydefinition', Analyzer::WITHOUT_CONSTANTS)
              ->_as('property')
              ->raw(<<<'GREMLIN'
 sideEffect{ 
     b = it.get().value("fullcode").tokenize(' = ');
     name = b[0];
}

GREMLIN
)
              ->initVariable('hasDefault',     '0')
              ->raw(<<<'GREMLIN'
      where( __.out('DEFAULT').hasLabel('Void').sideEffect{ hasDefault = 0;}.fold())
     .where( __.out('DEFAULT').not(where(__.in("RIGHT"))).not(__.hasLabel('Void')).sideEffect{ hasDefault = 1; init = it.get().value("fullcode"); if (it.get().label() in ["Integer", "Null", "Float", "Boolean", "String", "Heredoc"]) { expression = 0; } else { expression = 1;  }}.fold())
GREMLIN)

              ->inIs('PPP')

              ->initVariable('x_static',       'it.get().properties("static").any()')
              ->initVariable('x_visibility',   'it.get().value("visibility")')
              ->initVariable('x_var',          'it.get().value("token") == "T_VAR"')
              ->initVariable('readonly',       'it.get().properties("readonly").any()')
              ->initVariable('file',           '""')
              ->initVariable('expression',     '""')
              ->initVariable('init',           '""')
              ->initVariable('typehint_hints', '[]')
              ->initVariable('typehint_fnp',   '[]')
              ->initVariable('typehint_type',  'it.get().value("typehint")')

              ->collectOut('phpdoc',     'PHPDOC')

              ->raw(<<<'GREMLIN'
      where( __.out('TYPEHINT').not(hasLabel('Void')).not(__.in('DEFAULT')).sideEffect{ typehint_hints.add(it.get().value("fullcode")); typehint_fnp.add(it.get().value("fullnspath"));}.fold())
GREMLIN)

              ->filter(
                  $query->side()
                        ->inIs('PPP')
                        ->atomIs(array('Class', 'Interface', 'Trait'))
                        ->savePropertyAs('fullnspath',    'classe')
                        ->savePropertyAs('line',          'classline')
              )

              ->selectMap(array(
                    'class'         => 'classe',
                    'static'        => 'x_static',
                    'visibility'    => 'x_visibility',
                    'var'           => 'x_var',
                    'line'          => 'it.get().value("line")',
                    'name'          => 'name',
                    'value'         => 'init',
                    'hasDefault'    => 'hasDefault',
                    'expression'    => 'expression',
                    'phpdoc'        => 'phpdoc',
                    'classline'     => 'classline',
                    'readonly'      => 'readonly',
                    'typehint'      => 'typehint_hints',
                    'typehint_fnp'  => 'typehint_fnp',
                    'typehint_type' => 'typehint_type',
                    )
                );

        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $toDump        = array();
        $toPhpdoc      = array();
        $toTypehints   = array();
        foreach($result->toArray() as $row) {
            $row['visibility'] = $row['visibility'] === 'none' ? '' : $row['visibility'];

            // If we haven't found any definition for this class, just ignore it.
            if (!isset($citId[$row['classline'] . $row['class']])) {
                continue;
            }
            $propertyId = $row['class'] . '::' . $row['name'];
            if (isset($this->propertyIds[$propertyId])) {
                continue; // skip double
            }
            $this->propertyIds[$propertyId] = ++$this->propertyCount;

            $toDump[] = array($this->propertyCount,
                              $row['name'],
                              (int) $citId[$row['classline'] . $row['class']],
                              $row['visibility'],
                              $row['var'],
                              (int) $row['static'],
                              (int) $row['readonly'],
                              (int) $row['hasDefault'],
                              $row['value'],
                              $row['expression'],
                              $row['line'],
                              $row['typehint_type'],
            );

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'property',
                                        $this->propertyCount,
                                        $phpdoc
                                        );
                }
            }

            if (!empty($row['typehint'])) {
                foreach($row['typehint'] as $id => $typehint) {
                    $toTypehints[] = array(0,
                                           'property',
                                           $this->propertyCount,
                                           $typehint,
                                           $row['typehint_fnp'][$id],
                                           );
                }
            }
        }

        $total = $this->storeToDumpArray('properties', $toDump);
        $this->storeToDumpArray('phpdoc',    $toPhpdoc);
        $this->storeToDumpArray('typehints', $toTypehints);
        display("$total properties\n");

        // Class Constant
        $query = $this->newQuery('cit constants');
        $query->atomIs(array('Class', 'Classanonymous', 'Interface', 'Enum'), Analyzer::WITHOUT_CONSTANTS)
              ->savePropertyAs('line', 'ligne')
              ->savePropertyAs('line', 'classline')
              ->savePropertyAs('fullnspath', 'classe')
              ->outIs('CONST')
              ->savePropertyAs('visibility', 'visibilite')
              ->outIs('CONST')

              ->collectOut('phpdoc',     'PHPDOC')
              ->collectOut('attributes', 'ATTRIBUTE')

              ->collectOut('name', 'NAME', 'fullcode')
              ->collectOut('valeur', 'VALUE', 'fullcode')

              ->selectMap(array('name'       => 'name[0]',
                                'value'      => 'valeur[0]',
                                'visibility' => 'visibilite',
                                'class'      => 'classe',
                                'phpdoc'     => 'phpdoc',
                                'line'       => 'ligne',
                                'classline'  => 'classline',
                                'attributes' => 'attributes',
                               ));

        $query->prepareRawQuery();
        $classConstants = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $toDump          = array();
        $toAttributes    = array();
        $toPhpdoc        = array();
        foreach($classConstants as $row) {
            $row['visibility'] = $row['visibility'] === 'none' ? '' : $row['visibility'];

            // If we haven't found any definition for this class, just ignore it.
            if (!isset($citId[$row['classline'] . $row['class']])) {
                continue;
            }
            $classConstId = $row['class'] . '::' . $row['name'];
            if (isset($this->classConstIds[$classConstId])) {
                continue; // skip double
            }
            $this->classConstIds[$classConstId] = ++$this->classConstCount;

            $toDump[] = array($this->classConstCount,
                              $row['name'],
                              (int) $citId[$row['classline'] . $row['class']],
                              $row['visibility'],
                              $row['value'],
            );

            if (!empty($row['attributes'])) {
                foreach($row['attributes'] as $attribute) {
                    $toAttributes[] = array(0,
                                            'classconstant',
                                            $this->classConstCount,
                                            $attribute);
                }
            }

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'classconstant',
                                        $this->classConstCount,
                                        $phpdoc
                                        );
                }
            }
        }

        $total = $this->storeToDumpArray('classconstants', $toDump);
        $this->storeToDumpArray('attributes', $toAttributes);
        $this->storeToDumpArray('phpdoc',     $toPhpdoc);
        display("$total class constants\n");

        // Cases for enumeration
        $query = $this->newQuery('cit case enumeration');
        $query->atomIs(array('Enum'), Analyzer::WITHOUT_CONSTANTS)
              ->savePropertyAs('line',       'ligne')
              ->savePropertyAs('line',       'classline')
              ->savePropertyAs('fullnspath', 'classe')
              ->outIs('ENUMCASE')

              ->collectOut('phpdoc',     'PHPDOC'                  )
              ->collectOut('name',       'NAME',      'fullcode'   )
              ->collectOut('valeur',     'DEFAULT',   'noDelimiter')
              ->collectOut('attributes', 'ATTRIBUTE', 'fullcode'   )

              ->selectMap( array('name'       => 'name[0]',
                                 'value'      => 'valeur[0]',
                                 'visibility' => "'case'",
                                 'class'      => 'classe',
                                 'phpdoc'     => 'phpdoc',
                                 'line'       => 'ligne',
                                 'classline'  => 'classline',
                                 'attributes' => 'attributes',
                                 ));
        $query->prepareRawQuery();
        $cases = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $toDump     = array();
        $toPhpdoc   = array();
        foreach($cases as $row) {
            // If we haven't found any definition for this class, just ignore it.
            if (!isset($citId[$row['classline'] . $row['class']])) {
                print 'Ignoring ' . $row['classline'] . $row['class'] . PHP_EOL;
                continue;
            }
            $classConstId = $row['class'] . '::' . $row['name'];
            if (isset($this->classConstIds[$classConstId])) {
                continue; // skip double
            }
            $this->classConstIds[$classConstId] = ++$this->classConstCount;

            $toDump[] = array($this->classConstCount,
                              $row['name'],
                              (int) $citId[$row['classline'] . $row['class']],
                              $row['visibility'],
                              $row['value'] ?? 'none',
            );

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'enumcase',
                                        $this->classConstCount,
                                        $phpdoc
                                        );
                }
            }
        }

        $total = $this->storeToDumpArray('classconstants', $toDump);
        $this->storeToDumpArray('phpdoc', $toPhpdoc);
        display("$total enumerations cases\n");

        // Global Constants
        $query = $this->newQuery('Constants define()');
        $query->atomIs('Defineconstant', Analyzer::WITHOUT_CONSTANTS)
              ->collectOut('phpdoc',      'PHPDOC')
              ->initVariable('file',      '""')
              ->initVariable('namespace', '"\\\\"')

              ->raw(<<<'GREMLIN'
 where( 
    __.in().emit().repeat( __.inE().not(hasLabel("DEFINITION")).outV()).until(hasLabel("File"))
           .coalesce( 
                __.hasLabel("File").sideEffect{ file = it.get().value("fullcode"); },
                __.hasLabel("Namespace").sideEffect{ namespace = it.get().value("fullnspath"); }
                )
           .fold() 
)
GREMLIN
)
              ->filter(
                $query->side()
                     ->outIs('NAME')
                     ->is('constant', true)
                     ->savePropertyAs('fullcode', 'name')
              )
              ->filter(
                $query->side()
                     ->outIs('VALUE')
                     ->is('constant', true)
                     ->savePropertyAs('fullcode', 'v')
                     ->raw(<<<'GREMLIN'
 sideEffect{ if (it.get().label() in ["Integer", "Null", "Float", "Boolean", "String", "Heredoc"]) { 
    expression = 0; 
 } else { 
    expression = 1; 
}}
GREMLIN
)
              )
             ->selectMap(array('name'       => 'name',
                               'value'      => 'v',
                               'namespace'  => 'namespace',
                               'file'       => 'file',
                               'type'       => '"define"',
                               'phpdoc'     => 'phpdoc',
                               'expression' => 'expression',
                              ));
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $toDump       = array();
        $toPhpdoc     = array();
        foreach($result->toArray() as $row) {
            if (isset($namespacesId[$row['namespace'] . '\\'])) {
                $namespaceId = $namespacesId[$row['namespace'] . '\\'];
            } else {
                $namespaceId = 1;
            }

            $toDump[] = array(++$this->constantCount,
                              trim($row['name'], "'\""),
                              $namespaceId,
                              $this->files[$row['file']],
                              $row['value'],
                              $row['type'],
                              $row['expression'],
            );

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'const',
                                        $this->constantCount,
                                        $phpdoc
                                        );
                }
            }
        }
        $total = $this->storeToDumpArray('constants', $toDump);
        $this->storeToDumpArray('phpdoc', $toPhpdoc);
        display("$total global constants\n");

        $query = $this->newQuery('Constants const');
        $query->atomIs('Const', Analyzer::WITHOUT_CONSTANTS)
              ->collectOut('phpdoc',     'PHPDOC')
              ->initVariable('file',      '""')
              ->initVariable('namespace', '"\\\\"')

              ->raw(<<<'GREMLIN'
 where( 
    __.in().emit().repeat( __.inE().not(hasLabel("DEFINITION")).outV()).until(hasLabel("File"))
           .coalesce( 
                __.hasLabel("File").sideEffect{ file = it.get().value("fullcode"); },
                __.hasLabel("Namespace").sideEffect{ namespace = it.get().value("fullnspath"); }
                )
           .fold() 
)
GREMLIN
)
              ->hasNoIn('CONST') // Not class or interface
              ->outIs('CONST')
              ->atomIs('Constant', Analyzer::WITHOUT_CONSTANTS)
              ->filter(
                $query->side()
                     ->outIs('NAME')
                     ->is('constant', true)
                     ->savePropertyAs('fullcode', 'name')
              )
              ->filter(
                $query->side()
                     ->outIs('VALUE')
                     ->is('constant', true)
                     ->savePropertyAs('fullcode', 'v')
                     ->raw('sideEffect{ if (it.get().label() in ["Integer", "Null", "Float", "Boolean", "String", "Heredoc"]) { expression = 0; } else { expression = 1; }}')
              )

              ->selectMap(array('name'       => 'name',
                                'value'      => 'v',
                                'namespace'  => 'namespace',
                                'file'       => 'file',
                                'type'       => '"const"',
                                'phpdoc'     => 'phpdoc',
                                'expression' => 'expression',
                           ));
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $toDump   = array();
        $toPhpdoc = array();
        foreach($result->toArray() as $row) {
            $toDump[] = array(++$this->constantCount,
                              $row['name'],
                              $namespacesId[$row['namespace'] . '\\'] ?? 1,
                              $this->files[$row['file']],
                              $row['value'],
                              $row['type'],
                              $row['expression']
                            );

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'const',
                                        $this->constantCount,
                                        $phpdoc
                                        );
                }
            }
        }

        $total = $this->storeToDumpArray('constants', $toDump);
        $this->storeToDumpArray('phpdoc', $toPhpdoc);
        display("$total const constants\n");

        // Collect Functions
        // Functions
        $query = $this->newQuery('Functions');
        $query->atomIs(array('Function', 'Closure', 'Arrowfunction'), Analyzer::WITHOUT_CONSTANTS)

              ->initVariable('lines',          '[it.get().value("line")]')
              ->initVariable('x_reference',    'it.get().properties("reference").any()')
              ->initVariable('x_fullnspath',   'it.get().value("fullnspath");')
              ->initVariable('returntype_fnp', '[]')
              ->initVariable('returntype',     '[]')
              ->initVariable('returntype_type','it.get().value("typehint")')

              ->collectOut('phpdoc',     'PHPDOC')
              ->collectOut('attributes', 'ATTRIBUTE')
              ->collectOut('name',       'NAME',      'fullcode')

              ->initVariable('file',      '""')
              ->initVariable('namespace', '"\\\\"')

              ->raw(<<<GREMLIN
 where( 
    __.out("BLOCK").has("line").sideEffect{ lines.add(it.get().value("line")); }
      .out("EXPRESSION").emit().repeat( __.out({$this->linksDown})).times($MAX_LOOPING).has("line")
      .sideEffect{ lines.add(it.get().value("line")); }
      .fold()
 )
.where( __.out('RETURNTYPE').not(hasLabel('Void')).sideEffect{ returntype.add(it.get().value("fullcode"));returntype_fnp.add(it.get().value("fullnspath"));}.fold())
GREMLIN
)
              ->raw(<<<'GREMLIN'
 where( 
    __.in().emit().repeat( __.inE().not(hasLabel("DEFINITION")).outV()).until(hasLabel("File"))
           .coalesce( 
                __.hasLabel("File").sideEffect{ file = it.get().value("fullcode"); },
                __.hasLabel("Namespace").sideEffect{ namespace = it.get().value("fullnspath"); }
                )
           .fold() 
)
GREMLIN
)
              ->selectMap(array(
      'name'             => 'name[0]',
      'type'             => 'it.get().label().toString().toLowerCase()',
      'line'             => 'it.get().value("line")',
      'file'             => 'file',
      'namespace'        => 'namespace',
      'fullnspath'       => 'x_fullnspath',
      'reference'        => 'x_reference',
      'returntype'       => 'returntype',
      'returntype_fnp'   => 'returntype_fnp',
      'returntype_type'  => 'returntype_type',
      'begin'            => 'lines.min()',
      'end'              => 'lines.max()',
      'phpdoc'           => 'phpdoc',
      'attributes'       => 'attributes',
));
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $toDump       = array();
        $toAttributes = array();
        $toTypehints  = array();
        $toPhpdoc     = array();
        $unique       = array();
        foreach($result->toArray() as $row) {
            if (isset($unique[$row['name'] . $row['line']])) {
                continue;  // Skipping double definitions until we can differentiate them.
            }
            $unique[$row['name'] . $row['line']] = 1;

            if (strpos($row['fullnspath'], '@') === false) {
                $this->methodIds[$row['fullnspath']] = ++$this->methodCount;
                $n = $row['namespace'];
                if ($n[-1] !== '\\') {
                    $n .= '\\';
                }
                $ns = preg_grep('%^' . addslashes($n) . '$%i', array_keys($namespacesId));
                $k = array_pop($ns);

                $ns = $namespacesId[$k];
            } else {
                $this->methodIds[$row['fullnspath']] = ++$this->methodCount;
                // case of closure or arrow function
                $ns = '';
            }

            $toDump[] = array($this->methodCount,
                              $row['name'] ?? '',
                              $row['type'],
                              $ns,
                              $row['returntype_type'],
                              (int) $row['reference'],
                              $this->files[$row['file']] ?? '',
                              (int) $row['begin'],
                              (int) $row['end'],
                              );

            if (!empty($row['attributes'])) {
                foreach($row['attributes'] as $attribute) {
                    $toAttributes[] = array(0,
                                            $row['type'],
                                            $this->methodCount,
                                            $attribute);
                }
            }

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'function',
                                        $this->methodCount,
                                        $phpdoc
                                        );
                }
            }

            if (!empty($row['returntype'])) {
                foreach($row['returntype'] as $id => $typehint) {
                    $toTypehints[] = array(0,
                                           'function',
                                           $this->methodCount,
                                           $typehint,
                                           $row['returntype_fnp'][$id],
                                           );
                }
            }
        }

        $this->dump->cleanTable('functions');
        $total = $this->storeToDumpArray('functions', $toDump);
        $this->storeToDumpArray('typehints',  $toTypehints);
        $this->storeToDumpArray('phpdoc',     $toPhpdoc);
        $this->storeToDumpArray('attributes', $toAttributes);
        display("$total functions\n");

        // Functions parameters
        $query = $this->newQuery('Function parameters');
        $query->atomIs('Parameter', Analyzer::WITHOUT_CONSTANTS)

              ->initVariable('init',           '""')
              ->initVariable('hasDefault',     '0')
              ->initVariable('expression',     '""')
              ->initVariable('typehint_hints', '[]')
              ->initVariable('typehint_fnp',   '[]')
              ->initVariable('typehint_type',  'it.get().value("typehint")')

              ->collectOut('phpdoc',     'PHPDOC')
              ->collectOut('attributes', 'ATTRIBUTE')

              ->inIs('ARGUMENT')
              ->atomIs(array('Function', 'Closure', 'Arrowfunction'), Analyzer::WITHOUT_CONSTANTS)
              ->raw(<<<'GREMLIN'
where( __.sideEffect{ fonction = it.get().label().toString().toLowerCase(); 
                      fullnspath = it.get().value("fullnspath");  
                    }.fold() 
      )
.where( __.out('NAME')
         .sideEffect{ fonction = it.get().value("fullcode").toString().toLowerCase();}
         .fold()
     )
.sideEffect{ classe = it.get().value("fullnspath")}

.select('first')

.where( __.out('NAME').sideEffect{ name = it.get().value("fullcode")}.fold())
.where( __.out('TYPEHINT').not(hasLabel('Void')).not(__.in('DEFAULT')).sideEffect{ typehint_hints.add(it.get().value("fullcode")); typehint_fnp.add(it.get().value("fullnspath"));}.fold())
.where( __.out('DEFAULT').hasLabel('Void').sideEffect{ hasDefault = 0;}.fold())
.where( __.out('DEFAULT').not(where(__.in("RIGHT"))).not(hasLabel('Void')).sideEffect{ hasDefault = 1;}.sideEffect{ init = it.get().value("fullcode"); if (! it.get().label() in ["Integer", "Null", "Float", "Boolean", "String", "Heredoc"]) { expression = 1;  }}.fold())
GREMLIN
)
        ->selectMap(array(
         'name'          => 'name',
         'fullnspath'    => 'fullnspath',
         'rank'          => 'it.get().value("rank")',
         'variadic'      => 'it.get().properties("variadic").any()',
         'reference'     => 'it.get().properties("reference").any()',
         'line'          => 'it.get().value("line")',
         'function'      => 'fonction',
         'init'          => 'init',
         'expression'    => 'expression',
         'hasDefault'    => 'hasDefault',
         'typehint'      => 'typehint_hints',
         'typehint_fnp'  => 'typehint_fnp',
         'typehint_type' => 'typehint_type',
         'phpdoc'        => 'phpdoc',
         'attributes'    => 'attributes',
        ));

        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());

        $toDump       = array();
        $toPhpdoc     = array();
        $toAttributes = array();
        $toTypehints  = array();
        foreach($result->toArray() as $row) {
            // Those were skipped in the previous loop
            if (!isset($this->methodIds[$row['fullnspath']])) {
                continue;
            }

            $toDump[] = array( ++$this->argumentsId,
                               $row['name'],
                               0,
                               $this->methodIds[$row['fullnspath']],
                               (int) $row['rank'],
                               (int) $row['reference'],
                               (int) $row['variadic'],
                               $row['init'],
                               $row['expression'],
                               $row['hasDefault'],
                               (int) $row['line'],
                               $row['typehint_type'],
            );

            if (!empty($row['attributes'])) {
                foreach($row['attributes'] as $attribute) {
                    $toAttributes[] = array(0,
                                            'argument',
                                            $this->argumentsId,
                                            $attribute);
                }
            }

            if (!empty($row['phpdoc'])) {
                foreach($row['phpdoc'] as $phpdoc) {
                    $toPhpdoc[] = array(0,
                                        'function',
                                        $this->argumentsId,
                                        $phpdoc
                                        );
                }
            }

            if (!empty($row['typehint'])) {
                foreach($row['typehint'] as $id => $typehint) {
                    $toTypehints[] = array(0,
                                           'argument',
                                           $this->argumentsId,
                                           $typehint,
                                           $row['typehint_fnp'][$id],
                                           );
                }
            }
        }

        $total = $this->storeToDumpArray('arguments', $toDump);
        $this->storeToDumpArray('attributes', $toAttributes);
        $this->storeToDumpArray('typehints',  $toTypehints);
        $this->storeToDumpArray('phpdoc',     $toPhpdoc);
        display("$total function arguments\n");
    }

    private function collectStructures_namespaces(): array {
        $query = $this->newQuery('collectStructures_namespaces');
        $query->atomIs('Namespace', Analyzer::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->initVariable('name', 'it.get().value("fullcode") == " " ? "\\\\" : "\\\\" + it.get().value("fullcode") + "\\\\"')
              ->getVariable('name')
              ->unique();
        $query->prepareRawQuery();
        $namespaces = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $namespaces->string2Array();

        $this->dump->storeInTable('namespaces', $namespaces);

        $namespacesId = $this->dump->fetchTable('namespaces');
        $namespacesId->map(function (array $x): array { $x['namespace'] = mb_strtolower($x['namespace']); return $x; });
        return $namespacesId->toHash('namespace', 'id');
    }

    private function collectFiles(): void {
        $this->files = $this->dump->fetchTable('files')->toHash('file', 'id');
    }

    private function collectHashCounts(Query $query, string $name): void {
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $index = $result->toArray()[0] ?? array();

        $toDump = array();
        foreach($index as $number => $count) {
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

        $functioncallCount  = $this->gremlin->query('g.V().hasLabel("Functioncall").count()')[0];
        $functioncallMissed = $this->gremlin->query('g.V().hasLabel("Functioncall")
             .has("token", within("T_STRING", "T_NS_SEPARATOR"))
             .not(where(__.in("DEFINITION")))
             .not( __.has("isPhp", true))
             .not( __.has("isExt", true))
             .where( __.out("NAME").hasLabel("Identifier", "Nsname", "Name"))
        ');
        if ($functioncallMissed === 0) {
            file_put_contents("{$this->config->log_dir}/functions.missing.txt", 'Nothing found');
            $functioncallMissed = 0;
        } else {
            file_put_contents("{$this->config->log_dir}/functions.missing.txt", implode(PHP_EOL, array_column($functioncallMissed->toArray(), 'fullcode')));
            $functioncallMissed = $functioncallMissed->toInt();
        }
        $toDump[] = array('',
                          'functioncall total',
                          $functioncallCount,
                          );
        $toDump[] = array('',
                          'functioncall missed',
                          $functioncallMissed,
                          );

        $methodCount  = $this->gremlin->query('g.V().hasLabel("Methodcall").count()')[0];
        $methodMissed = $this->gremlin->query('g.V().hasLabel("Methodcall")
             .not(where(__.in("DEFINITION")))
             .where( __.out("METHOD").has("token", "T_STRING"))
        ');
        if (is_array($methodMissed)) {
            file_put_contents("{$this->config->log_dir}/methodcall.missing.txt", implode(PHP_EOL, array_column($methodMissed->toArray(), 'fullcode')));
            $methodMissed = $methodMissed->toInt();
        } else {
            file_put_contents("{$this->config->log_dir}/methodcall.missing.txt", 'Nothing found');
            $methodMissed = 0;
        }
        $toDump[] = array('',
                          'methodcall total',
                          $methodCount,
                          );
        $toDump[] = array('',
                          'methodcall missed',
                          $methodMissed,
                          );

        $memberCount  = $this->gremlin->query('g.V().hasLabel("Member").count()')[0];
        $memberMissed = $this->gremlin->query('g.V().hasLabel("Member")
             .not(where(__.in("DEFINITION")))
             .where( __.out("MEMBER").has("token", "T_STRING"))
        ');
        if (is_array($memberMissed)) {
            file_put_contents("{$this->config->log_dir}/members.missing.txt", implode(PHP_EOL, array_column($memberMissed->toArray(), 'fullcode')));
            $memberMissed = $memberMissed->toInt();
        } else {
            file_put_contents("{$this->config->log_dir}/members.missing.txt", 'Nothing found');
            $memberMissed = 0;
        }
        $toDump[] = array('',
                          'member total',
                          $memberCount,
                          );
        $toDump[] = array('',
                          'member missed',
                          $memberMissed,
                          );

        $staticMethodCount  = $this->gremlin->query('g.V().hasLabel("Staticmethodcall").count()')[0];
        $staticMethodMissed = $this->gremlin->query('g.V().hasLabel("Staticmethodcall")
             .not(where(__.in("DEFINITION")))
             .where( __.out("CLASS").hasLabel("Identifier", "Nsname", "Self", "Parent", "Static"))
             .not(where( __.out("CLASS").in("ANALYZED").has("analyzer", "Classes/IsExtClass")))
             .where( __.out("METHOD").has("token", "T_STRING"))
        ');
        if (is_array($staticMethodMissed)) {
            file_put_contents("{$this->config->log_dir}/staticmethodcall.missing.txt", implode(PHP_EOL, array_column($staticMethodMissed->toArray(), 'fullcode')));
            $staticMethodMissed = $staticMethodMissed->count();
        } else {
            file_put_contents("{$this->config->log_dir}/staticmethodcall.missing.txt", 'Nothing found');
            $staticMethodMissed = 0;
        }
        $toDump[] = array('',
                          'static methodcall total',
                          $staticMethodCount,
                          );
        $toDump[] = array('',
                          'static methodcall missed',
                          $staticMethodMissed,
                          );

        $staticConstantCount  = $this->gremlin->query('g.V().hasLabel("Staticonstant").count()')[0];
        $staticConstantMissed = $this->gremlin->query('g.V().hasLabel("Staticonstant")
             .not(where(__.in("DEFINITION")))
             .not(where( __.out("CLASS").in("ANALYZED").has("analyzer", "Classes/IsExtClass")))
             .where( __.out("METHOD").has("token", "T_STRING"))
        ');
        if (is_array($staticConstantMissed)) {
            file_put_contents("{$this->config->log_dir}/staticconstant.missing.txt", implode(PHP_EOL, array_column($staticConstantMissed->toArray(), 'fullcode')));
            $staticConstantMissed = $staticConstantMissed->toInt();
        } else {
            file_put_contents("{$this->config->log_dir}/staticconstant.missing.txt", 'Nothing found');
            $staticConstantMissed = 0;
        }
        $toDump[] = array('',
                          'static constant total',
                          $staticConstantCount,
                          );
        $toDump[] = array('',
                          'static constant missed',
                          $staticConstantMissed,
                          );

        $staticPropertyCount  = $this->gremlin->query('g.V().hasLabel("Staticproperty").count()')[0];
        $staticPropertyMissed = $this->gremlin->query('g.V().hasLabel("Staticproperty")
             .not(where(__.in("DEFINITION")))
             .not(where( __.out("CLASS").in("ANALYZED").has("analyzer", "Classes/IsExtClass")))
             .where( __.out("MEMBER").has("token", "T_VARIABLE"))
        ');
        if (is_array($staticPropertyMissed)) {
            file_put_contents("{$this->config->log_dir}/staticproperty.missing.txt", implode(PHP_EOL, array_column($staticPropertyMissed->toArray(), 'fullcode')));
            $staticPropertyMissed = $staticPropertyMissed->toInt();
        } else {
            file_put_contents("{$this->config->log_dir}/staticproperty.missing.txt", 'Nothing found');
            $staticPropertyMissed = 0;
        }
        $toDump[] = array('',
                          'static property total',
                          $staticPropertyCount,
                          );
        $toDump[] = array('',
                          'static property missed',
                          $staticPropertyMissed,
                          );

        $constantCounts = $this->gremlin->query('g.V().hasLabel("Identifier", "Nsname").count()')[0];
        $constantMissed = $this->gremlin->query('g.V().hasLabel("Identifier", "Nsname")
             .not(has("token", within("T_CONST", "T_FUNCTION")))
             .not(where(__.in("DEFINITION")))
             .not(where(__.in("ANALYZED").has("analyzer", "Constants/IsExtConstant")))
             .not(where(__.in("NAME").hasLabel("Class", "Defineconstant", "Namespace", "As")))
             .not(where(__.in("EXTENDS", "IMPLEMENTS").hasLabel("Class", "Classanonymous", "Interface")))
             .not(where(__.in().hasLabel("Analysis", "Instanceof", "As", "Staticmethod", "Usetrait", "Usenamespace", "Member", "Constant", "Functioncall", "Methodcallname", "Staticmethodcall", "Staticproperty", "Staticclass", "Staticconstant", "Catch", "Parameter")))
             ') ?: array();
        if (is_array($constantMissed)) {
            file_put_contents("{$this->config->log_dir}/constant.missing.txt", implode(PHP_EOL, array_column($constantMissed->toArray(), 'fullcode')));
            $constantMissed = $constantMissed->toInt();
        } else {
            file_put_contents("{$this->config->log_dir}/constant.missing.txt", 'Nothing found');
            $constantMissed = 0;
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

    public function checkRulesets($ruleset, array $analyzers): void {
        $sqliteFile = $this->config->dump;

        $sqlite = new \Sqlite3($sqliteFile);
        $sqlite->busyTimeout(\SQLITE3_BUSY_TIMEOUT);

        $query = 'SELECT analyzer FROM resultsCounts WHERE analyzer IN (' . makeList($analyzers) . ')';
        $ran = array();
        $res = $sqlite->query($query);
        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
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
        foreach($rulesets as $ruleset) {
            $analyzerList = $this->rulesets->getRulesetsAnalyzers(array($ruleset));

            $diff = array_diff($analyzerList, $analyzers);
            $diff = array_filter($diff, function (string $x): bool { return (substr($x, 0, 5) !== 'Dump/') && (substr($x, 0, 9) !== 'Complete/');  });
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
        $timer = new Timer();
        $this->collectFiles();

        $this->collectStructures();

        $timer->end();
        $this->log->log( 'Collected Structures: ' . number_format($timer->duration(Timer::MS), 2) . "ms\n");

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

        foreach($dumps as $dump) {
            include $dump;

            $this->dump->storeQueries($queries);
            unlink($dump);
        }
    }
}

?>