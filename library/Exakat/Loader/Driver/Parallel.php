<?php declare(strict_types = 1);
/*
 * Copyright 2012-2024 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


namespace Exakat\Loader\Driver;

use SplFileObject;
use Exakat\Graph\Graph;
use Exakat\Helpers\Timer;
use Symfony\Component\Process\Process;
use const PARALLEL_WAIT_MS;

class Parallel extends Driver {
    private const FINISH			  = null;

    private string		  $tmpPath;
    private string		  $installationPath;
    private Graph		  $graphdb;
    private SplFileObject $log;
    private array		  $processes = array();
    private int		      $max       = 4;

    private int		      $fragment = 1;
    private int		      $total    = 0;
    private array		  $append   = array();
    private string		  $filePath = '';

    // @todo : ensure that processes are not mixed between the different loads. One must be finished before the next.

    public function __construct(string $tmpPath, string $installationPath, Graph $graphdb, SplFileObject $log, int $max) {
        $this->tmpPath 			= $tmpPath;
        $this->installationPath = $installationPath;
        $this->graphdb 			= $graphdb;
        $this->log 				= $log;

        $this->info['loader mode'] = 'parallel';
        $this->info['loader max loaders'] = $this->max;

        $this->filePath = "{$this->tmpPath}/graphdb." . $this->fragment . '.graphson';
    }

    public function saveNodes(?string $row = self::FINISH): void {
        if ($row !== null) {
            if (empty($row)) {
                return;
            }

            $this->append[] = $row;
            ++$this->total;

            if ($this->total <= self::LOAD_CHUNK) {
                return;
            }
        }

        $size = file_put_contents($this->filePath, implode(PHP_EOL, $this->append) . PHP_EOL, \FILE_APPEND);

        $this->monitorProcesses();

        $timer = new Timer();
        // @todo check that this works with phar
        $process = new Process(array('php',
                                '-r',
                                'include("' . $this->installationPath . '/server/load.php");',
                                $this->filePath,
                                $this->graphdb->getHost(),
                                $this->graphdb->getPort(),
                                $this->installationPath,
                                // @todo replace dirname with the actual path
                                dirname($this->tmpPath) . '/log/parallel.log',
                              ));
        $this->processes[] = $process;
        $process->start();

        $timer->end();
        $this->log("path\t$this->total\t$size\t" . $timer->duration());

        $this->append = array();
        $this->total = 0;
        ++$this->fragment;
        $this->filePath = "{$this->tmpPath}/graphdb." . $this->fragment . '.graphson';
    }

    public function saveLinkGremlin(array $links): void {
        $chunks = array_chunk($links, self::LOAD_CHUNK_LINK);

        foreach ($chunks as $chunk) {
            $this->saveNodeLinksGremlin($chunk);
        }
    }

    private function saveNodeLinksGremlin(array $links): void {
        $timer = new Timer();

        ++$this->fragment;
        $path = $this->tmpPath . '/graphdb.' . $this->fragment . '.tmp';
        foreach ($links as &$link) {
            $link = $link->toString();
        }
        unset($link);
        file_put_contents($path, join(PHP_EOL, $links));

        $this->monitorProcesses();

        // @todo This should not be 'php' but the actual php binary from the config
        $process = new Process(array('php',
                                '-r',
                                'include("' . $this->installationPath . '/server/loadLink.php");',
                                $path,
                                $this->graphdb->getHost(),
                                $this->graphdb->getPort(),
                                $this->installationPath,
                                // @todo replace dirname with the actual path
                                dirname($this->tmpPath) . '/log/parallel.log',
                              ));
        $this->processes[] = $process;
        $process->start();

        $timer->end();
        $this->log("links\t{$this->fragment}\t" . $timer->duration());
    }

    public function savePropertiesGremlin(string $attribute, array $properties): void {
        $chunks = array_chunk($properties, self::LOAD_CHUNK_PROPERTY);

        foreach ($chunks as $chunk) {
            $this->savePropertiesGremlinByChunk($attribute, $chunk);
        }
    }

    public function savePropertiesGremlinByChunk(string $attribute, array $chunk): void {
        $timer = new Timer();

        ++$this->fragment;
        $path = "{$this->tmpPath}/graphdb." . $attribute . '.' . $this->fragment . '.tmp';
        file_put_contents($path, join(',', $chunk));

        $this->monitorProcesses();

        $process = new Process(array('php',
                                '-r',
                                'include("' . $this->installationPath . '/server/loadProperty.php");',
                                $attribute,
                                $path,
                                $this->graphdb->getHost(),
                                $this->graphdb->getPort(),
                                $this->installationPath,
                                // @todo replace dirname with the actual path
                                dirname($this->tmpPath) . '/log/parallel.log',
                                ));
        $this->processes[] = $process;
        $process->start();

        $timer->end();

        $this->log("properties\t$attribute\t" . count($chunk) . "\t" . $timer->duration());
    }

    private function monitorProcesses(): void {
        do {
            foreach ($this->processes as $id => $p) {
                if (!$p->isRunning()) {
                    unset($this->processes[$id]);
                }
            }
            usleep(PARALLEL_WAIT_MS);
        } while (count($this->processes) >= $this->max);
    }

    public function finish(): void {
        display(count($this->processes) . " process to finish\n");

        $this->monitorProcesses();

        // @todo : set a limit for waiting ?
        while (!empty($this->processes)) {
            $this->monitorProcesses();
            usleep(PARALLEL_WAIT_MS);
        }

        display("Process finished\n");
    }

    private function log(string $message): void {
        $this->log->fwrite($message . PHP_EOL);
    }
}

?>
