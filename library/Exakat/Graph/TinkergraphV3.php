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

use Exakat\Graph\Helpers\GraphResults;
use Exakat\Exceptions\GremlinException;
use Exakat\Exceptions\UnknownGremlinVersion;
use Brightzone\GremlinDriver\Connection;
use Exakat\Helpers\Timer;

class TinkergraphV3 extends Graph {
    private const SUPPORTED_VERSIONS = array('3.4', '3.5', '3.6');
    private string $gremlinVersion = '3.6';

    private Connection $db;

    public function init(): void {
        if (!file_exists("{$this->config->tinkergraphv3_folder}/lib/")) {
            // No local production, just skip init.
            $this->status = self::UNAVAILABLE;
            return;
        }

        $gremlinJar = glob("{$this->config->tinkergraphv3_folder}/lib/gremlin-core-*.jar");
        $gremlinVersion = basename(array_pop($gremlinJar) ?? '');
        //gremlin-core-3.4.10.jar
        preg_match('/gremlin-core-([0-9.]+)\.\d+.jar/', $gremlinVersion, $r);
        $gremlinVersion = $r[1] ?? 'unknown gremlin version';
        $stats['gremlin version'] = $gremlinVersion;
        $this->gremlinVersion = $gremlinVersion;

        if (!in_array($this->gremlinVersion, self::SUPPORTED_VERSIONS, STRICT_COMPARISON)) {
            throw new UnknownGremlinVersion($this->gremlinVersion);
        }

        $this->db = new Connection(array( 'host'     => $this->config->tinkergraphv3_host,
                                          'port'     => $this->config->tinkergraphv3_port,
                                          'graph'    => 'graph',
                                          'emptySet' => true,
                                   ) );
        $this->db->message->registerSerializer('\Exakat\Graph\Helpers\GraphsonV3', true);
        $this->status = self::UNCHECKED;
    }

    public function getInfo(): array {
        $stats = array();

        if (empty($this->config->tinkergraphv3_folder)) {
            $stats['configured'] = 'No tinkergraph configured in config/exakat.ini.';
        } elseif (!file_exists($this->config->tinkergraphv3_folder)) {
            $stats['installed'] = 'No (folder : ' . $this->config->tinkergraphv3_folder . ')';
        } else {
            $stats['installed'] = 'Yes (folder : ' . $this->config->tinkergraphv3_folder . ')';
            $stats['host'] = $this->config->tinkergraphv3_host;
            $stats['port'] = $this->config->tinkergraphv3_port;

            $gremlinJar = glob("{$this->config->tinkergraphv3_folder}/lib/gremlin-core-*.jar");
            $gremlinVersion = basename(array_pop($gremlinJar) ?? '');
            //example : gremlin-core-3.2.5.jar
            $gremlinVersion = substr($gremlinVersion, 13, -4);

            $stats['gremlin version'] = $gremlinVersion;

            if (file_exists("{$this->config->tinkergraphv3_port}/db/tinkergraph.pid")) {
                $stats['running'] = 'Yes (PID : ' . trim(file_get_contents("{$this->config->tinkergraphv3_port}/db/tinkergraph.pid")) . ')';
            }
        }

        return $stats;
    }

    private function checkConfiguration(): void {
        ini_set('default_socket_timeout', '1600');
        $this->db->open();
    }

    public function query(string $query, array $params = array(),array $load = array()): GraphResults {
        if ($this->status === self::UNAVAILABLE) {
            return new GraphResults();
        } elseif ($this->status === self::UNCHECKED) {
            $this->checkConfiguration();
        }

        foreach ($params as $name => $value) {
            $this->db->message->bindValue($name, $value);
        }

        $result = $this->db->send($query);

        return new GraphResults($result);
    }

    public function queryOne(string $query, array $params = array(),array $load = array()): GraphResults {
        if ($this->status === self::UNCHECKED) {
            $this->checkConfiguration();
        }

        return $this->query($query, $params, $load);
    }

    public function checkConnection(): bool {
        $res = @stream_socket_client('tcp://' . $this->config->tinkergraphv3_host . ':' . $this->config->tinkergraphv3_port,
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
        // This is memory only Database
        $this->stop();
        $this->start();
    }

    public function setConfigFile(): void {
        if (!file_exists("{$this->config->tinkergraphv3_folder}/conf/tinkergraphv3.{$this->gremlinVersion}.yaml")) {
            copy("{$this->config->dir_root}/server/tinkergraphv3/tinkergraphv3.{$this->gremlinVersion}.yaml",
                "{$this->config->tinkergraphv3_folder}/conf/tinkergraphv3.{$this->gremlinVersion}.yaml");
        }
    }

    public function start(): void {
        if (!file_exists("{$this->config->tinkergraphv3_folder}/conf")) {
            throw new GremlinException('No tinkgergraph configuration folder found.');
        }

        $this->setConfigFile();

        if (in_array($this->gremlinVersion, self::SUPPORTED_VERSIONS, STRICT_COMPARISON)) {
            display("start gremlin server {$this->gremlinVersion}.x TinkergraphV3");
            exec("cd {$this->config->tinkergraphv3_folder}; rm -rf db/neo4j; GREMLIN_YAML=conf/tinkergraphv3.{$this->gremlinVersion}.yaml /bin/bash  ./bin/gremlin-server.sh start > gremlin.log 2>&1 &");
        } else {
            throw new GremlinException("Wrong version for tinkergraph V3 : $this->gremlinVersion");
        }
        $this->init();
        sleep(1);

        $timer = new Timer();
        $round = 0;
        do {
            $connexion = $this->checkConnection();
            if (!$connexion) {
                ++$round;
                usleep(100000 * $round);
            }
        } while (!$connexion && $round < 60);
        $timer->end();

        display("Restarted in $round rounds\n");

        if (file_exists("{$this->config->tinkergraphv3_folder}/run/gremlin.pid")) {
            $pid = trim(file_get_contents("{$this->config->tinkergraphv3_folder}/run/gremlin.pid"));
        } else {
            $pid = false;
        }

        $ms = number_format($timer->duration(Timer::MS), 2);
        $pid = $pid === false ? 'Not found' : $pid;
        display("started [$pid] in $ms ms");
    }

    public function stop(): void {
        if (file_exists("{$this->config->tinkergraphv3_folder}/run/gremlin.pid")) {
            display("Stopping gremlin server {$this->gremlinVersion}");
            $res = shell_exec("cd {$this->config->tinkergraphv3_folder}; GREMLIN_YAML=conf/tinkergraphv3.{$this->gremlinVersion}.yaml ./bin/gremlin-server.sh stop");
            if (preg_match('/\[(\d+)\]/', $res, $r)) {
                display("Stopped gremlin server [$r[1]]");
            } else {
                display("Could not stop gremlin server : $res");
            }
        } else {
            display('Gremlin server is not running');
        }
    }

    public function getDefinitionSQL(): string {
        // Optimize loading by sorting the results
        return <<<'SQL'
SELECT DISTINCT CASE WHEN definitions.id IS NULL THEN definitions2.id ELSE definitions.id END AS definition, GROUP_CONCAT(DISTINCT calls.id) AS call, count(calls.id) AS id
FROM calls
LEFT JOIN definitions 
    ON definitions.type       = calls.type       AND
       definitions.fullnspath = calls.fullnspath
LEFT JOIN definitions definitions2
    ON definitions2.type       = calls.type       AND
       definitions2.fullnspath = calls.globalpath 
WHERE (definitions.id IS NOT NULL OR definitions2.id IS NOT NULL)
GROUP BY definition
SQL;
    }

    public function getGlobalsSql(): string {
        return 'SELECT origin, destination FROM globals';
    }
}

?>
