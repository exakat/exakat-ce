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


namespace Exakat\Loader\Driver;

use SplFileObject;
use Exakat\Graph\Graph;
use Exakat\Helpers\Timer;

class Serial extends Driver {
    private const FINISH			 = null;

    private string		  $path;
    private string		  $installationPath;
    private Graph		  $graphdb;
    private SplFileObject $log;
    private array		  $processes = array();

    private int		      $fragment = 1;
    private int		      $total = 0;
    private array		  $append = array();
    private string		  $filePath = '';

    // @todo : ensure that processes are not mixed between the different loads. One must be finished before the next.

    public function __construct(string $path, string $installationPath, Graph $graphdb, SplFileObject $log) {
        $this->path 			= $path;
        $this->installationPath = $installationPath;
        $this->graphdb 			= $graphdb;
        $this->log 				= $log;

        $this->info['loader mode'] = 'serial';

        $this->filePath = "{$this->path}/graphdb." . $this->fragment . '.graphson';
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
        $timer = new Timer();
        $this->graphdb->query("graph.io(IoCore.graphson()).readGraph(\"{$this->filePath}\");");

        $timer->end();
        $this->log("path\t$this->total\t$size\t" . $timer->duration());

        $this->append = array();
        $this->total = 0;
        unlink($this->filePath);
        ++$this->fragment;
        $this->filePath = "{$this->path}/graphdb." . $this->fragment . '.graphson';
    }

    public function savePropertiesGremlin(string $attribute, array $properties): void {
        $chunks = array_chunk($properties, self::LOAD_CHUNK_PROPERTY);
        foreach ($chunks as $chunk) {
            $this->savePropertiesGremlinByChunk($attribute, $chunk);
        }
    }

    public function saveLinkGremlin(array $links): void {
        $chunks = array_chunk($links, self::LOAD_CHUNK_LINK);

        foreach ($chunks as $chunk) {
            $this->saveNodeLinksGremlin($chunk);
        }
    }

    private function saveNodeLinksGremlin(array $links): void {
        $timer = new Timer();

        $query = <<<'GREMLIN'
links.each { link ->
    g.V(link[1]).addE(link[0]).to( __.V(link[2])).iterate();
}

GREMLIN;
        $this->graphdb->query($query, array('links' => $links));
        $timer->end();

        ++$this->fragment;
        $this->log("links\t{$this->fragment}\t" . $timer->duration());
    }

    private function savePropertiesGremlinByChunk(string $attribute, array $chunk): void {
        ++$this->fragment;

        $timer = new Timer();
        $query = <<<'GREMLIN'
    g.V(vertices).property(property, true).iterate();
GREMLIN;
        $this->graphdb->query($query, array('property' => $attribute, 'vertices' => $chunk));

        $timer->end();

        $this->log("properties\t$attribute\t" . count($chunk) . "\t" . $timer->duration());
    }

    public function finish(): void {
        // Nothing to do
    }

    private function log(string $message): void {
        $this->log->fwrite($message . PHP_EOL);
    }
}

?>
