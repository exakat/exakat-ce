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

namespace Exakat\Graph;

use Exakat\Graph\Helpers\GraphResults;
use Exakat\Config;

abstract class Graph {
    protected Config $config;

    public const CHECKED     = 1;
    public const UNCHECKED   = 0;
    public const UNAVAILABLE = 2;

    public const DRIVERS = array('tinkergraph' => 'TinkergraphV3',
                                 'gsneo4j'	   => 'GSNeo4jV3',
                                 'nogremlin'   => 'NoGremlin',
                                );

    protected int    $status           = self::UNCHECKED;
    protected string $path             = '';

    protected string $host             = '';
    protected string $port             = '';
    protected string $folder           = '';

    public const GRAPHDB = array('nogremlin',
                                 'gsneo4jV3',
                                 'tinkergraphv3',
                                 );

    public function __construct(Config $config) {
        $this->config = $config;
        $this->path = $this->config->{static::CONFIG_PREFIX . '_folder'} ?? '';
    }

    abstract public function query(string $query, array $params = array(),array $load = array()): GraphResults;

    abstract public function queryOne(string $query, array $params = array(),array $load = array()): GraphResults;

    abstract public function init(): void;

    abstract public function getInfo(): array;

    abstract public function start(): void;

    abstract public function stop(): void;

    public function restart(): void {
        $this->stop();
        $this->start();
    }

    abstract public function serverInfo(): array;

    abstract public function checkConnection(): bool;

    abstract public function clean(): void;

    public function empty(): void {
        $res = $this->query('g.V().count();');
        display('Emptying with ' . $res->toInt() . " nodes\n");

        // don't catch max(id) here
        $b = hrtime(true);
        $this->query('g.V().drop();');
        $this->query('g.E().drop();');
        $e = hrtime(true);

        display('Emptying in ' . number_format(($e - $b) / 1000000) . " ms\n");
    }

    // Produces an id for storing a new value.
    // null means that the graph will handle it.
    // This is not the case of all graph : tinkergraph doesn't.
    public function getId() {
        return 'null';
    }

    public static function getConnexion(Config $config, string $gremlin): self {
        $graphDBClass = "\\Exakat\\Graph\\$gremlin";
        assert(class_exists($graphDBClass), "No such class as $gremlin\n");

        return new $graphDBClass($config);
    }

    public function setConfigFile(): void {
        if (!file_exists($this->path)) {
            return;
        }

        if (!file_exists($this->path . '/db')) {
            mkdir($this->path . '/db', 0755);
        }
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getPort(): string {
        return $this->port;
    }

    public function getFolder(): string {
        return $this->folder;
    }
}

?>
