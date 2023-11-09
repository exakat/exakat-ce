<?php declare(strict_types = 1);
/*
 * Copyright 2012-2022 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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
use Exakat\Tasks\Helpers\Lock;
use Exakat\Helpers\Timer;
use Exakat\Exceptions\NeedsAnalyzerThema;
use Exakat\Exceptions\NoSuchAnalyzer;
use Exakat\Exceptions\NoSuchProject;
use Exakat\Exceptions\InvalidProjectName;
use Exakat\Exceptions\NoSuchRuleset;
use Exakat\Exceptions\ProjectNeeded;
use Exakat\Exceptions\QueryException;
use Exakat\Exceptions\MissingGremlin;
use Exakat\Exceptions\DSLException;
use ProgressBar\Manager as ProgressBar;
use Exakat\Phpexec;
use Exakat\Log;
use Exception;
use Generator;
use Symfony\Component\Process\Process;
use Brightzone\GremlinDriver\ServerException;
use const PARALLEL_WAIT_MS;

class Analyze extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    private ProgressBar $progressBar;
    private Phpexec $php;
    private array $analyzed  = array();
    private array $analyzers = array();
    private array $processes = array();

    private array $dependencies = array();

    private string $mode = 'serial';

    public function run(): void {
        if (!$this->config->project->validate()) {
            throw new InvalidProjectName($this->config->project->getError());
        }

        if ($this->config->project->isDefault()) {
            throw new ProjectNeeded();
        }

        if ($this->config->gremlin === 'NoGremlin') {
            throw new MissingGremlin();
        }

        if (!file_exists($this->config->project_dir)) {
            throw new NoSuchProject((string) $this->config->project);
        }

        $this->checkTokenLimit();

        // Forcing mode
        if ($this->config->loader_mode === 'serial' || $this->config->parallel < 2) {
            $this->mode = 'serial';
        } else {
            $this->mode = 'parallel';
        }

        $timer = new Timer();

        // Take this before we clean it up
        $this->checkAnalyzed();

        $this->logname = $this->config->logFile ?: str_replace(' ', '_', implode('-', $this->config->project_rulesets));

        if (!empty($this->config->program)) {
            if (is_array($this->config->program)) {
                $analyzersClass = $this->config->program;
            } else {
                $analyzersClass = array($this->config->program);
            }

            foreach ($analyzersClass as $analyzer) {
                if ($this->rulesets->getClass($analyzer) === '') {
                    throw new NoSuchAnalyzer($analyzer, $this->rulesets);
                }
            }

            // Forcing mode
            $this->mode = 'serial';
            if ($this->logname === '') {
                $this->logname = 'single';
            }
        } elseif (!empty($this->config->project_rulesets)) {
            $ruleset = $this->config->project_rulesets;

            if ($ruleset[0] === 'Complete') {
                $all = $this->config->projectConfig ?? $this->config->exakatConfig;

                $ruleset = array();
                foreach ($all->project_rulesets as $rule) {
                    if (empty($this->datastore->getHash(trim($rule, '"')))) {
                        $ruleset[] = $rule;
                    }
                }

                // drop the first one, as it may be already running
                array_shift($ruleset);
                if (empty($ruleset)) {
                    display('All needed ruleset are done. Aborting.');
                    die();
                }

                print 'Completing the following rulesets ' . implode(', ', $ruleset) . PHP_EOL;
            }

            if ((!$analyzersClass = $this->rulesets->getRulesetsAnalyzers($ruleset)) && ($ruleset[0] !== 'None')) {
                throw new NoSuchRuleset(implode(', ', $ruleset), $this->rulesets->getSuggestionRuleset($ruleset));
            }

            // @todo : unidentified rules are omitted at execution time
            // @todo : check that norefresh still works well
            // may be we could spot them here, and fix or report

            $this->datastore->addRow('hash', array(implode('-', $this->config->project_rulesets) => count($analyzersClass) ) );
        } else {
            throw new NeedsAnalyzerThema();
        }

        $this->log = new Log('analyze.' . $this->logname,
            "{$this->config->projects_root}/projects/{$this->config->project}");

        $this->log->log('Analyzing project ' . (string) $this->config->project);
        $this->log->log("Runnable analyzers\t" . count($analyzersClass));

        $this->php = exakat('php');

        $original = array_flip($analyzersClass);
        if ($this->config->verbose && !$this->config->quiet) {
            $this->progressBar = new Progressbar(0, count($original), $this->config->screen_cols);
        }

        if ($this->config->noDependencies) {
            $this->dependencies = array_fill_keys($analyzersClass, array());
        } else {
            $analyzersClass = $this->getAndSortDependencies($analyzersClass);
        }

        $rounds = 0;
        while (!empty($this->dependencies)) {
            $analyzersClass = array_diff($analyzersClass, array_keys($this->analyzers));
            foreach ($analyzersClass as $analyzer) {
                if ($this->analyze($analyzer)) {
                    if ($this->config->verbose && !$this->config->quiet && isset($original[$analyzer])) {
                        echo $this->progressBar->advance();
                        unset($original[$analyzer]);
                    }
                }
            }

            $this->finish();
            ++$rounds;

            if ($rounds > 10) {
                display('Stopping dependencies : ' . count($this->dependencies) . ' left : ' . join(', ', $this->dependencies));
                $this->log->log('Stopping dependencies : ' . count($this->dependencies) . ' left : ' . join(', ', $this->dependencies));
                $this->log->log(var_export($this->dependencies));
                break 1;
            }

            display("$rounds) ==================\n");
        }

        $timer->end();
        $this->log->log('Duration : ' . number_format($timer->duration(Timer::MS), 2));
        $this->log->log('Memory : ' . memory_get_usage(true));
        $this->log->log('Memory peak : ' . memory_get_peak_usage(true));

        display("Done\n");
    }

    private function fetchAnalyzers(string $analyzerClass): Analyzer {
        if (isset($this->analyzers[$analyzerClass])) {
            return $this->analyzers[$analyzerClass];
        }

        return $this->rulesets->getInstance($analyzerClass);
    }
    /*

        if (isset($this->analyzed[$analyzerClass]) &&
            $this->config->noRefresh === true) {
            display("$analyzerClass is already processed\n");

            return ;
        }

        if (!empty($this->config->rules_version_max) &&
            version_compare($this->config->rules_version_max,$this->analyzers[$analyzerClass]->getExakatSince()) < 0) {
            display("$analyzerClass is too new (rules_version_max: {$this->config->rules_version_max})\n");
            return;
        }

        if (!empty($this->config->rules_version_min) &&
            version_compare($this->config->rules_version_min,$this->analyzers[$analyzerClass]->getExakatSince()) > 0) {
            display("$analyzerClass is too old (rules_version_min: {$this->config->rules_version_min})\n");
            return;
        }

        if ($this->config->noDependencies === true) {
            $dependencies[$analyzerClass] = array();
            return;
        } 
        
        $dependsOn = $this->analyzers[$analyzerClass]->dependsOn();
        if (empty($dependsOn)) { 
            $dependencies[$analyzerClass] = $dependsOn;
            return; 
        }

        // @todo : move this to a external script : This should not be done beyond development
        foreach($dependsOn as $e) {
            assert($this->rulesets->getClass($e) !== '', "No such analyzer as $e in {$analyzerClass}\n");
        }
        $dependencies[$analyzerClass] = $dependsOn;

        $diff = array_diff($dependsOn, array_keys($this->analyzers));
        foreach ($diff as $d) {
            if (!isset($this->analyzers[$d])) {
                $this->fetchAnalyzers($d, $dependencies);
            }
        }
    }
*/
    private function getAndSortDependencies(array $analyzers): array {
        $stock = $analyzers;

        while (!empty($stock)) {
            $analyzerName = array_pop($stock);

            $analyzer = $this->fetchAnalyzers($analyzerName);

            $this->dependencies[$analyzerName] = $analyzer->dependsOn();
            $stock = array_merge($stock, $analyzer->dependsOn());

            foreach ($analyzer->dependsOn() as $y) {
                if (!isset($this->dependencies[$y])) {
                    $this->dependencies[$y] = array();
                }
            }
        }

        $sorted = array();
        $dep2 = $this->dependencies;
        foreach ($this->foo($dep2) as $y) {
            $sorted[] = $y;
        }

        return $sorted;
    }

    public function foo(array &$dependencies, $what = null): Generator {
        foreach ($dependencies as $v => &$d) {
            if ($what !== null && $v !== $what) {
                continue;
            }

            if (empty($d)) {
                yield $v;
                unset($dependencies[$v]);
                continue;
            }

            foreach ($d as $i => $e) {
                if (!isset($dependencies[$e])) {
                    unset($d[$i]);
                    continue;
                }

                if (empty($dependencies[$e])) {
                    yield $e;
                    unset($d[$i]);
                    unset($dependencies[$e]);
                    continue;
                }

                yield from $this->foo($dependencies, $d[$i]);
            }
        }
        unset($d);

        if (!empty($dependencies)) {
            yield from $this->foo($dependencies);
        }
    }

    private function analyze(string $analyzerClass): bool {
        // processing and the list of dependencies
        if (isset($this->analyzers[$analyzerClass])) {
            unset($this->dependencies[$analyzerClass]);
            return false;
        }

        $analyzer = $this->fetchAnalyzers($analyzerClass);

        if ($this->mode === 'serial') {
            $this->analyzeSingle($analyzer, $analyzerClass);
            return true;
        } else {
            return $this->analyzeParallel($analyzer, $analyzerClass);
        }
    }

    private function monitorProcesses(): void {
        do {
            foreach ($this->processes as $id => $p) {
                if (!$p->isRunning()) {
                    unset($this->processes[$id]);
                    $this->analyzers[$id] = 1;

                    // That should always be an empty array
                    assert(empty($this->dependencies[$id]), "$id is not empty\n");
                    unset($this->dependencies[$id]);

                    foreach ($this->dependencies as $name => $d) {
                        foreach ($d as $i => $j) {
                            if ($j === $id) {
                                unset($this->dependencies[$name][$i]);
                            }
                        }
                    }
                }
            }
        } while (count($this->processes) >= $this->config->parallel);
    }

    public function finish(string $analyzerClass = ''): void {
        $this->monitorProcesses();

        if (empty($analyzerClass)) {
            // @todo : set a limit for waiting ?
            while (!empty($this->processes)) {
                $this->monitorProcesses();
                usleep(PARALLEL_WAIT_MS);
            }
        } else {
            while (!isset($this->processes[$analyzerClass])) {
                $this->monitorProcesses();
                usleep(PARALLEL_WAIT_MS);
            }
        }
    }

    private function analyzeParallel(Analyzer $analyzer, string $analyzerClass): bool {
        if (!empty($this->dependencies[$analyzerClass])) {
            return false;
        }

        $process = new Process(array('php',
                                'exakat',
                                'analyze',
                                '-p',
                                $this->config->project,
                                '-P',
                                $analyzer->getShortAnalyzer(),
                                '-v',
                                '--nodep',
                                '--norefresh',
                                '--parallel',
                                '1',
                                '--logFile',
                                $this->logname,
                                ));
        $process->start();
        $this->processes[$analyzerClass] = $process;

        $this->monitorProcesses();
        return true;
    }

    private function analyzeSingle(Analyzer $analyzer, string $analyzerClass): int {
        $timer = new Timer();

        $lock = new Lock($this->config->tmp_dir, $analyzerClass);
        // @todo : this doesn't check if the folder is available => fail immediately
        if (!$lock->check()) {
            display("Concurency lock activated for $analyzerClass\n");

            return 0;
        }

        if (isset($this->analyzed[$analyzerClass]) && $this->config->noRefresh === true) {
            display( "$analyzerClass is already processed (1)\n");
            unset($this->dependencies[$analyzerClass]);
            return $this->analyzed[$analyzerClass];
        }

        $analyzer->init();

        $total_results = 0;
        if (!$analyzer->checkPhpVersion($this->config->phpversion)) {
            $analyzerQuoted = $analyzer->getInBaseName();

            $analyzer->storeError('Not Compatible With PHP Version', Analyzer::VERSION_INCOMPATIBLE);
            unset($this->dependencies[$analyzerClass]);

            display("$analyzerQuoted is not compatible with PHP version {$this->config->phpversion}. Ignoring\n");
        } elseif (!$analyzer->checkPhpConfiguration($this->php)) {
            $analyzerQuoted = $analyzer->getInBaseName();

            $analyzer->storeError('Not Compatible With PHP Configuration', Analyzer::CONFIGURATION_INCOMPATIBLE);
            unset($this->dependencies[$analyzerClass]);

            display( "$analyzerQuoted is not compatible with PHP configuration of this version. Ignoring\n");
        } else {
            display( "$analyzerClass running\n");
            try {
                $analyzer->run();
            } catch (DSLException $e) {
                $timer->end();
                display( "$analyzerClass : DSL building exception\n");
                display($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                $this->log->log("$analyzerClass\t" . ($timer->duration()) . "\terror : " . $e->getMessage());
                $this->datastore->addRow('analyzed', array($analyzerClass => 0 ) );
                $this->checkAnalyzed();
            } catch (QueryException $e) {
                $timer->end();
                display("$analyzerClass : Query running exception\n");
                display($e->getMessage());
                $this->log->log("$analyzerClass\t" . ($timer->duration()) . "\terror : " . $e->getMessage());
                $counts = $this->gremlin->query('g.V().hasLabel("Analysis").has("analyzer", "' . $analyzer->getInBaseName() . '").property("count", __.out("ANALYZED").count()).values("count")')->toInt();
                $this->datastore->addRow('analyzed', array($analyzerClass => $counts ) );
                $this->checkAnalyzed();
            } catch (ServerException $e) {
                $timer->end();
                display("$analyzerClass : Server running exception\n");
                display($e->getMessage());
                $this->log->log("$analyzerClass\t" . ($timer->duration()) . "\terror : " . $e->getMessage());
                $counts = $this->gremlin->query('g.V().hasLabel("Analysis").has("analyzer", "' . $analyzer->getInBaseName() . '").property("count", __.out("ANALYZED").count()).values("count")')->toInt();
                $this->datastore->addRow('analyzed', array($analyzerClass => $counts ) );
                $this->checkAnalyzed();
            } catch (Exception $e) {
                $timer->end();
                display( "$analyzerClass : generic exception " . get_class($e) . "\n");
                $this->log->log("$analyzerClass\t" . ($timer->duration()) . "\texception : " . $e::class . "\terror : " . $e->getMessage() . "\tfile : " . $e->getFile() . ':' . $e->getLine());
                if (str_contains($e->getMessage(), 'The server exceeded one of the timeout settings ')  ) {
                    $counts = $this->gremlin->query('g.V().hasLabel("Analysis").has("analyzer", "' . $analyzer->getInBaseName() . '").property("count", __.out("ANALYZED").count()).values("count")')->toInt();
                    $this->datastore->addRow('analyzed', array($analyzerClass => $counts ) );
                } else {
                    display($e->getMessage());
                    $this->datastore->addRow('analyzed', array($analyzerClass => 0 ) );
                }
                $this->checkAnalyzed();

                return 0;
            }

            $total_results = $analyzer->getRowCount();
            $processed     = $analyzer->getProcessedCount();
            $queries       = $analyzer->getQueryCount();
            $rawQueries    = $analyzer->getRawQueryCount();

            display( "$analyzerClass run ($total_results / $processed)\n");
            $timer->end();
            $this->log->log("$analyzerClass\t" . ($timer->duration()) . "\t$total_results\t$processed\t$queries\t$rawQueries");
            // storing the number of row found in Hash table (datastore)
            $this->datastore->addRow('analyzed', array($analyzerClass => $total_results ) );

            // This also counts the analysis that don't leave data in the database.
            $this->analyzed[$analyzerClass] = $total_results;
            unset($this->dependencies[$analyzerClass]);
        }

        $this->analyzers[$analyzerClass] = 1;

        $this->checkAnalyzed();

        return $total_results;
    }

    private function checkAnalyzed(): void {
        $query = <<<'GREMLIN'
g.V().hasLabel("Analysis").as("analyzer", "count").select("analyzer", "count").by("analyzer").by("count");
GREMLIN;
        $res = $this->gremlin->query($query);

        foreach ($res as list('analyzer' => $analyzer, 'count' => $count)) {
            if ($count != -1) {
                $this->analyzed[$analyzer] = $count;
            }
        }
    }
}

?>
