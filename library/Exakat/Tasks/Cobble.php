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

use Exakat\Cobbler\Cobbler;
use Exakat\Config;
use Exakat\Vcs\Vcs;
use Exakat\Exceptions\NeedsAnalyzerThema;
use Exakat\Exceptions\NoSuchCobbler;
use Exakat\Exceptions\NoCodeInProject;
use Exakat\Exceptions\ProjectNeeded;
use Exakat\Helpers\Timer;

class Cobble extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    private Timer $timer;

    public function __construct() {
        parent::__construct();
        $this->timer = new Timer();
    }

    public function run(): void {
        if ($this->config->project->isDefault()) {
            throw new ProjectNeeded();
        }

        if (empty($this->config->program)) {
            throw new NeedsAnalyzerThema();
        }

        if (is_array($this->config->program)) {
            $cobblersClass = $this->config->program;
        } else {
            $cobblersClass = array($this->config->program);
        }
        
        // @todo check for format now

        foreach ($cobblersClass as $cobbler) {
            if (!Cobbler::getClass($cobbler)) {
                throw new NoSuchCobbler($cobbler);
            }
        }

        $res = $this->gremlin->query('g.V().hasLabel("Project").valueMap(true)');

        if (isset($res[0]['code']) && $res[0]['code'][0] !== (string) $this->config->project) {
            display("Not the right project for cobbling :  {$res[0]['code'][0]} / {$this->config->project}. Must restart.\n");
            shell_exec($this->config->php . ' exakat cleandb -Q -v -stop ');
        }

        $vcs = Vcs::getVcs($this->config);
        if ($vcs !== \Exakat\Vcs\Git::class) {
            print "Cobbling only support git ($vcs).\n";

            return;
        }

		// @todo : make this call getinstance 
        $vcs = new $vcs((string) $this->config->project, $this->config->code_dir);
        if (!empty($this->config->branch) && $vcs->hasBranch($this->config->branch)) {
            print 'Branch already exists : cannot overwrite an existing branch. Please, change branch name or remove branch first.';

            return;
        }

        $this->logTime('Start');
        if ($res->count() === 0) {
            display('Running files' . PHP_EOL);
            $analyze = new Files(self::IS_SUBTASK);
            $config = $this->config->duplicate(array('json' => false));
            $analyze->setConfig($config);
            $analyze->run();
            unset($analyze);
            $this->addSnitch(array('step'    => 'Files',
                                   'project' => $this->config->project));

            display('Loading files' . PHP_EOL);
            $load = new Load(self::IS_SUBTASK);
            $load->setWS();
            $load->run();
            unset($load);
            $this->addSnitch(array('step'    => 'Load',
                                   'project' => $this->config->project));
        }

        $loc = $this->datastore->getHash('loc');
        if (intval($loc) === 0) {
            throw new NoCodeInProject((string) $this->config->project);
        }

        if (empty($this->config->program)) {
            throw new NeedsAnalyzerThema();
        }

        $this->logTime('Cobbling');
        foreach ($this->config->program as $program) {
            $this->logTime('Cobbling ' . $program);
            $cobbler = Cobbler::getInstance($program, $this->config);

            if (!$this->config->noDependencies && !empty($cobbler->dependsOn())) {
                display('Analysis : ' . implode(', ', $cobbler->dependsOn()) . "\n");
                $configThema = $this->config->duplicate(array(
                    'norefresh' => true,
                    'program' => $cobbler->dependsOn(),
                    )
                );

                $analyze = new Analyze(self::IS_SUBTASK);
                $analyze->setConfig($configThema);
                $analyze->run();
            }

            display("Cobbling\n");
            $cobbler->run();

            $total_results = $cobbler->getRowCount();
            $processed     = $cobbler->getProcessedCount();
            $queries       = $cobbler->getQueryCount();
            $rawQueries    = $cobbler->getRawQueryCount();

            display( "$program run ($total_results / $processed)\n");
        }

        display("Export\n");
        $this->logTime('Export');
        $args = array ( 1 => 'export',
                        2 => '-p',
                        3 => (string) $this->config->project,
                        4 => '--format',
                        5 => 'Php',
                        );
        foreach ($this->config->program as $c) {
            $args[] = '-P';
            $args[] = $c;
        }

        if (!empty($this->config->inplace)) {
            $args[] = '--inplace';
        } elseif (!empty($this->config->json)) {
            $args[] = '-json';
        } elseif (!empty($this->config->dirname)) {
            $args[] = '-d';
            $args[] = $this->config->dirname;
        } elseif (!empty($this->config->filename)) {
            $args[] = '-f';
            $args[] = $this->config->filename[0] . '.out';
        } elseif (!empty($this->config->branch)) {
            $args[] = '--branch';
            $args[] = $this->config->branch;
        } elseif (!empty($cobbler->getTargetBranch())) {
            $args[] = '--branch';
            $args[] = $cobbler->getTargetBranch();
        } else {
            die('Must config target destination. Aborting.');
        }

        $configThema = new Config($args);

        $export = new Export(self::IS_SUBTASK);
        $export->setConfig($configThema);
        $export->run();

        $this->logTime('End');
    }

    private function logTime(string $step): void {
        static $log;

        if ($log === null) {
            $log = fopen("{$this->config->log_dir}/cobbling.timing.csv", 'a');
        }
        if ($log === false) {
            // can't log so just skip it.
            return;
        }

        $this->timer->end();

        fwrite($log, $step . "\t" . $this->timer->duration() . PHP_EOL);
        $this->timer = new Timer();
    }
}
?>
