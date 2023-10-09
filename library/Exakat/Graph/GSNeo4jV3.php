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

namespace Exakat\Graph;

use Exakat\Helpers\Timer;
use Exakat\Graph\Helpers\GraphResults;
use Exakat\Exceptions\GremlinException;
use Brightzone\GremlinDriver\Connection;
use Brightzone\GremlinDriver\ServerException;
use Exakat\Exceptions\UnknownGremlinVersion;

class GSNeo4jV3 extends Graph {
    public const GREMLIN_VERSIONS  = array('3.4', '3.5', '3.6', '3.7');
    public const CONFIG_PREFIX     = 'gsneo4jv3';

    public const GREMLIN_PLUGINS_COUNTS  = array('3.4' => 72,
                                                 '3.5' => 72,
                                                 '3.6' => 106,
                                                 '3.7' => 106,
                                                 );
    private string     $gremlinVersion = '3.6';
    private Connection $db;

    public function getInfo(): array {
        $stats = array();

        if (empty($this->path)) {
            $stats['configured'] = 'No gsneo4jv3_folder configured in config/exakat.ini.';
        } elseif (!file_exists($this->path)) {
            $stats['installed'] = 'No (folder : ' . $this->path . ')';
        } elseif (!file_exists($this->path . '/ext/neo4j-gremlin/')) {
            $stats['installed'] = 'Partially (missing neo4j folder : ' . $this->path . ')';
        } else {
            $stats['installed'] = "Yes (folder : {$this->path})";
            $stats['host'] = $this->config->gsneo4jv3_host;
            $stats['port'] = $this->config->gsneo4jv3_port;

            $plugins = glob("{$this->path}/ext/neo4j-gremlin/plugin/*.jar");
            if (!in_array(count($plugins), self::GREMLIN_PLUGINS_COUNTS)) {
                $stats['grapes failed'] = 'Partially installed neo4j plugin. Please, check installation docs, and "grab" again : some of the files are missing for neo4j.';
            }

            $gremlinJar = glob("{$this->path}/lib/gremlin-core-*.jar");
            $gremlinVersion = basename(array_pop($gremlinJar) ?? '');
            preg_match('/gremlin-core-([0-9.]+)\.\d+.jar/', $gremlinVersion, $r);
            $gremlinVersion = $r[1] ?? 'unknown gremlin version';
            $stats['gremlin version'] = $gremlinVersion;
            $this->gremlinVersion = $gremlinVersion;

            $neo4jJar = glob("{$this->path}/ext/neo4j-gremlin/lib/neo4j-*.jar");
            $neo4jJar = array_filter($neo4jJar, function (string $x): int {
                return preg_match('#/neo4j-\d\.\d\.\d\.jar#', $x);
            });
            $neo4jVersion = basename(array_pop($neo4jJar) ?? '');

            //neo4j-2.3.3.jar
            $neo4jVersion = substr($neo4jVersion, 6, -4);
            $stats['neo4j version'] = $neo4jVersion;

            if (file_exists("{$this->path}/db/gsneo4jv3.pid")) {
                $stats['running'] = 'Yes (PID : ' . trim(file_get_contents("{$this->path}/db/gsneo4jv3.pid")) . ')';
            }
        }

        return $stats;
    }

    public function init(): void {
        $this->host   = $this->config->gsneo4jv3_host;
        $this->port   = $this->config->gsneo4jv3_port;
        $this->folder = $this->config->gsneo4jv3_folder;

        if (!file_exists("{$this->folder}/lib/")) {
            // No local production, just skip init.
            $this->status = self::UNAVAILABLE;
            return;
        }

        $gremlinJar = glob("{$this->folder}/lib/gremlin-core-*.jar");
        $gremlinVersion = basename(array_pop($gremlinJar) ?? '');
        // 3.4/3.5/3.6
        preg_match('/gremlin-core-([0-9.]+)\.\d+.jar/', $gremlinVersion, $r);
        $gremlinVersion = $r[1] ?? 'unknown gremlin version';
        $stats['gremlin version'] = $gremlinVersion;
        $this->gremlinVersion = $gremlinVersion;

        if (!in_array($this->gremlinVersion, self::GREMLIN_VERSIONS, STRICT_COMPARISON)) {
            throw new UnknownGremlinVersion($this->gremlinVersion);
        }

        $this->db = new Connection(array( 'host'     => $this->host,
                                          'port'     => $this->port,
                                          'graph'    => 'graph',
                                          'emptySet' => true,
                                   ) );

        $this->db->message->registerSerializer('\Exakat\Graph\Helpers\GraphsonV3', true);
        $this->status = self::UNCHECKED;
    }

    private function checkConfiguration(): void {
        ini_set('default_socket_timeout', '1600');
        $this->db->open();
    }

    public function query(string $query, array $params = array(),array $load = array()): GraphResults {
        if ($this->status === self::UNAVAILABLE) {
            return new GraphResults();
        }

        if ($this->status === self::UNCHECKED) {
            $this->checkConfiguration();
        }

        $params['#jsr223.groovy.engine.keep.globals'] = 'phantom';
        foreach ($params as $name => $value) {
            $this->db->message->bindValue($name, $value);
        }

        $attempts = 0;
        do {
            try {
                $result = $this->db->send($query);
            } catch (ServerException $e) {
                print $e->getMessage();
                if (str_contains($e->getMessage(), 'could not be processed')) {
                    // This is a compilation error. Skip it all.
                    $attempts = 3;
                    $result = array();
                } else {
                    ++$attempts;
                }
            }
        } while (!isset($result) && $attempts < 3);

        return new GraphResults($result);
    }

    public function queryOne(string $query, array $params = array(),array $load = array()): GraphResults {
        if ($this->status === self::UNCHECKED) {
            $this->checkConfiguration();
        }

        return $this->query($query, $params, $load);
    }

    public function checkConnection(): bool {
        $res = @stream_socket_client("tcp://{$this->config->gsneo4jv3_host}:{$this->config->gsneo4jv3_port}",
            $errno,
            $errorMessage,
            1,
            STREAM_CLIENT_CONNECT
        );

        return is_resource($res);
    }

    public function serverInfo(): array {
        if ($this->status === self::UNCHECKED) {
            $this->checkConfiguration();
        }

        $res = $this->query('Gremlin.version();');

        return $res->toArray();
    }

    public function clean(): void {
        $this->stop();
        $this->start();
    }

    public function setConfigFile(): void {
        if (!file_exists("{$this->folder}/conf/gsneo4jv3.{$this->gremlinVersion}.yaml")) {
            assert(file_exists("{$this->config->dir_root}/server/gsneo4jv3/gsneo4jv3.{$this->gremlinVersion}.yaml"),
                "Missing gsneo4jv3.{$this->gremlinVersion}.yaml in server/gsneo4jv3");
            copy( "{$this->config->dir_root}/server/gsneo4jv3/gsneo4jv3.{$this->gremlinVersion}.yaml",
                "{$this->folder}/conf/gsneo4jv3.{$this->gremlinVersion}.yaml");
            copy( "{$this->config->dir_root}/server/gsneo4jv3/exakat.properties",
                "{$this->folder}/conf/exakat.properties");
        }
    }

    public function start(): void {
        if (!file_exists("{$this->folder}/conf")) {
            throw new GremlinException('No graphdb found.');
        }

        $this->setConfigFile();

        if (in_array($this->gremlinVersion, self::GREMLIN_VERSIONS)) {
            display("start gremlin server {$this->gremlinVersion}.x GSNeo4jV3");
            exec("cd {$this->folder}; rm -rf db/neo4j; GREMLIN_YAML=conf/gsneo4jv3.{$this->gremlinVersion}.yaml /bin/bash ./bin/gremlin-server.sh start");
        }
        display('started gremlin server');
        $this->init();
        sleep(2);

        $timer = new Timer();
        $round = 0;
        $pid = false;
        do {
            $connexion = $this->checkConnection();
            if (!$connexion) {
                ++$round;
                usleep(100000 * $round);
            }
        } while ( !$connexion && $round < 21);
        $timer->end();

        display("Restarted in $round rounds\n");

        if (file_exists("{$this->folder}/run/gremlin.pid")) {
            $pid = trim(file_get_contents("{$this->folder}/run/gremlin.pid"));
        } else {
            $pid = false;
        }

        $ms = number_format($timer->duration(Timer::MS), 2);
        $pid = $pid === false ? 'Not found' : $pid;
        display("started [$pid] in $ms ms");
    }

    public function stop(): void {
        if (file_exists("{$this->folder}/run/gremlin.pid")) {
            display("Stopping gremlin server {$this->gremlinVersion}");
            $res = shell_exec("cd {$this->folder}; GREMLIN_YAML=conf/gsneo4jv3.{$this->gremlinVersion}.yaml ./bin/gremlin-server.sh stop");
            if (preg_match('/\[(\d+)\]/', $res, $r)) {
                display("Stopped gremlin server [$r[1]]");
            } else {
                display("Could not stop gremlin server : $res");
            }
        } else {
            display('Gremlin server is not running');
        }
    }
}

?>
