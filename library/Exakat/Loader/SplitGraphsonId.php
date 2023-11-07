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


namespace Exakat\Loader;

use Sqlite3;
use stdClass;
use SplFileObject;
use Exakat\Config;
use Exakat\Graph\Graph;
use Exakat\Helpers\Timer;
use Exakat\Data\Collector;
use Exakat\Tasks\Helpers\Atom;
use Exakat\Loader\Driver\Driver;
use Exakat\Tasks\Helpers\AtomInterface;

// @todo : export process to a separate class, with a fall back on serial and inlined processing.

class SplitGraphsonId extends Loader {
    private const CSV_SEPARATOR = ',';
    private const LOAD_CHUNK          = 100000;
    private const DROP_EID_CHUNK      = 100000;

    private int $load_chunk = self::LOAD_CHUNK;

    private array $tokenCounts   = array('Project' => 1);

    private Config $config;

    private int   $id  = 1;
    private array $ids = array();

    private Graph  $graphdb;
    private Driver $driver;

    private string $path         = '';
    private int    $pathPart     = 0;
    private array  $processes    = array();
    private array  $links        = array();

    private int $total           = 0;
    private int $totalLink       = 0;

    private Collector $dictCode;

    private Sqlite3 $sqlite;
    private bool    $withWs = AtomInterface::WITHOUT_WS;

    private SplFileObject $log;

    private array $properties = array();

    public function __construct(Sqlite3 $sqlite, Atom $id0, bool $withWs = AtomInterface::WITHOUT_WS) {
        $this->config         = exakat('config');
        $this->graphdb        = exakat('graphdb');

        $this->sqlite         = $sqlite;
        $this->withWs 	      = $withWs;

        $this->dictCode       = new Collector();

        $this->log = new SplFileObject($this->config->log_dir . '/loader.timing.csv', 'w+');

        $this->cleanCsv();

        $jsonText = json_encode($id0->toGraphsonLine($this->id, $this->withWs));
        assert(!json_last_error(), 'Error encoding ' . $id0->atom . ' : ' . json_last_error_msg());

        $this->driver = Driver::getInstance($this->config->loader_mode,
            $this->config->tmp_dir,
            $this->graphdb,
            $this->log,
            (int) $this->config->loader_parallel_max
        );

        display('Loading with ' . $this->driver->getName() . PHP_EOL);

        $this->driver->saveNodes($jsonText);

        ++$this->total;
    }

    public function __destruct() {
        $this->cleanCsv();
    }

    public function finalize(array $relicat): bool {
        $this->driver->saveNodes();

        display("Init finalize\n");
        $this->driver->finish();
        display("Finished loadings\n");

        $totalDuration = new Timer();

        display("Fetch ID\n");
        $this->fetchIds();
        display("Save last nodes\n");
        $this->saveNodeLinks();
        display("Save properties\n");
        $this->saveProperties();
        display("Finish processes\n");
        $this->driver->finish();

        // Finish boolean properties
        // convert boolean from int to actual type of boolean
        do {
            $res = $this->graphdb->query('g.V().has("boolean", within(0, 1)).choose( __.values("boolean").is(eq(0)), __.property("boolean", false), __.property("boolean", true)).limit(' . self::LOAD_CHUNK . ').count();');
        } while ($res->toInt() === self::LOAD_CHUNK);

        $query = 'g.V().hasLabel("Project").id();';
        $res = $this->graphdb->query($query);
        $project_id = $res->toUuid();

        // @todo : move this to a Graph class method (it knows the project_id)
        $query = 'g.V().hasLabel("File").not(where( __.in("PROJECT"))).addE("PROJECT").from(__.V(' . $project_id . '));';
        $this->graphdb->query($query);

        $query = 'g.V().hasLabel("Virtualglobal").not(where( __.in("GLOBAL"))).addE("GLOBAL").from(__.V(' . $project_id . '));';
        $this->graphdb->query($query);

        assert(empty($relicat), "Relicat n'est pas vide\n" . print_r($relicat, true));

        // global variables to global variabldefintiion
        $query = <<<'GREMLIN'
g.V().hasLabel("Virtualglobal").as('id').as('code').select('id', 'code').by(id).by('code');
GREMLIN;
        $res = $this->graphdb->query($query);
        $vg = $res->toHash('code', 'id');

        // global variables to variabldefinition
        $query = <<<'GREMLIN'
g.V().hasLabel("File").out("DEFINITION").hasLabel("Variabledefinition").as('id').as('code').select('id', 'code').by(id).by('code');
GREMLIN;
        $res = $this->graphdb->query($query);
        $ids = array();
        $total = 0;
        foreach ($res->toArray() as $atom) {
            if (!isset($vg[$atom['code']])) {
                continue;
            }

            $ids[$vg[$atom['code']]][$atom['id']] = array($vg[$atom['code']], $atom['id']);
            ++$total;
        }

        // global variables to variabldefinition
        $query = <<<'GREMLIN'
g.V().hasLabel("Global").out("GLOBAL").in("DEFINITION").hasLabel("Variabledefinition").as('id').as('code').select('id', 'code').by(id).by('code');
GREMLIN;
        $res = $this->graphdb->query($query);
        foreach ($res->toArray() as $atom) {
            if (!isset($vg[$atom['code']])) {
                continue;
            }

            $ids[$vg[$atom['code']]][$atom['id']] = array($vg[$atom['code']], $atom['id']);
            ++$total;
        }

        display('GlobalVars start : ' . count(array_merge(...$ids)));
        $timer = new Timer();
        $query = <<<'GREMLIN'
globalVars.each { y ->
    g.V(y[0]).addE('DEFINITION').to(__.V(y[1])).iterate();
}
GREMLIN;
        $this->graphdb->query($query, array('globalVars' => array_merge(...$ids) ) );
        $timer->end();

        $this->log("globals\t$total\t" . $timer->duration());
        display('GlobalVars end');

        // $GLOBALS
        $query = <<<'GREMLIN'
g.V().hasLabel("Phpvariable").has("fullcode", "\$GLOBALS").values("code")
GREMLIN;
        $res = $this->graphdb->query($query);
        $GLOBALS_ID = $res->toInt();

        if (!empty($GLOBALS_ID)) {
            $query = <<<GREMLIN
g.V().hasLabel("Virtualglobal").has("code", $GLOBALS_ID).as("b").as("c")
 .V().hasLabel("Phpvariable").not(where(__.in("DEFINITION"))).as("a")
 .select("a","b").by("code")
 .where("a", eq("b")).select("c").addE("DEFINITION").to("a").count()
GREMLIN;
            $this->graphdb->query($query);
            $res = $this->graphdb->query($query);
        }

        // global variables to $GLOBALS['x']
        $query = <<<'GREMLIN'
g.V().hasLabel("Phpvariable").has("fullcode", "\$GLOBALS").in("VARIABLE").hasLabel("Array").has("globalvar").not(where(__.in("DEFINITION"))).as("a")
 .V().hasLabel("Virtualglobal").where(eq("a")).by("globalvar")
 .addE("DEFINITION").to("a").count()

GREMLIN;
        $res = $this->graphdb->query($query);
        display($res->toInt() . " \$GLOBALS['d']\n");

        // Read the ID in the database
        $definitionSQL = <<<'SQL'
SELECT DISTINCT CASE WHEN definitions.id IS NULL THEN definitions2.id ELSE definitions.id END AS definition, GROUP_CONCAT(DISTINCT calls.id) AS call
FROM calls
LEFT JOIN definitions 
    ON definitions.type       = calls.type       AND
       definitions.fullnspath = calls.fullnspath
LEFT JOIN definitions definitions2
    ON definitions2.type       = calls.type       AND
       definitions2.fullnspath = calls.globalpath 
WHERE (definitions.id IS NOT NULL OR definitions2.id IS NOT NULL) AND
        CASE WHEN definitions.id IS NULL THEN definitions2.id ELSE definitions.id END != calls.id
GROUP BY definition
SQL;
        $res = $this->sqlite->query($definitionSQL);
        // Fast dump, with a write to memory first
        $links = array();
        $chunk = 0;
        while ($row = $res->fetchArray(\SQLITE3_NUM)) {
            // Skip reflexive definitions, which never exist.
            if ($row[0] === $row[1]) {
                continue;
            }

            $row[0] = $this->ids[$row[0]];
            $r = explode(',', $row[1]);
            $chunk += count($r);
            $total += count($r);

            foreach ($r as $s) {
                $links[] = array('DEFINITION', $row[0], $this->ids[$s]);
                ++$chunk;
            }

            if ($chunk > $this->load_chunk) {
                $this->driver->saveLinkGremlin($links);
                $links = array();
                $chunk = 0;
            }
        }
        $this->driver->finish();

        if (empty($total)) {
            display('no definitions');
        } else {
            $this->driver->saveLinkGremlin($links);
            $this->driver->finish();
        }

        display('Drop Eid');
        $this->dropPropertyeId();

        display('Save Tokens Counts');
        $this->saveTokenCounts();

        $totalDuration->end();

        display('loaded nodes (duration : ' . $totalDuration->duration(Timer::MS) . ' ms)');

        $this->cleanCsv();
        display('Cleaning CSV');

        return true;
    }

    private function saveProperties(): void {
        // break down property files into small chunks for processing inside 300s.
        foreach ($this->properties as $attribute => $properties) {
            foreach ($properties as &$p) {
                $p = $this->ids[$p];
            }
            unset($p);

            $this->driver->savePropertiesGremlin($attribute, $properties);
        }

        $this->driver->finish();
    }

    private function cleanCsv(): void {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    private function saveTokenCounts(): void {
        $datastore = exakat('datastore');

        $datastore->addRow('tokenCounts', $this->tokenCounts);
    }

    public function saveFiles(string $exakatDir, array $atoms, array $links): void {
        $fileName = 'unknown';

        $json     = array();

        foreach ($atoms as $atom) {
            if ($atom->atom === 'File') {
                $fileName = $atom->code;
            }

            $json[$atom->id] = $atom->toGraphsonLine($this->id, $this->withWs);

            foreach ($atom->properties() as $property) {
                if (isset($this->tokenCounts[$property])) {
                    ++$this->tokenCounts[$property];
                } else {
                    $this->tokenCounts[$property] = 1;
                }
            }

            foreach ($atom->boolProperties() as $property) {
                if (isset($this->properties[$property])) {
                    $this->properties[$property][] = $atom->id;
                } else {
                    $this->properties[$property] = array($atom->id);
                }
            }
        }

        foreach ($links as &$link) {
            $this->tokenCounts[$link[0]] = ($this->tokenCounts[$link[0]] ?? 0) + 1;
        }
        unset($link);

        $total = 0; // local total
        $append = array();
        foreach ($json as $j) {
//            assert(isset($j->properties['code']), print_r($j, true));
            if (isset($j->properties['code'])) {
                $V = $j->properties['code'][0]->value;
                $j->properties['code'][0]->value = $this->dictCode->get($V);
            }

            if (isset($j->properties['lccode'][0]->value)) {
                $j->properties['lccode'][0]->value = $this->dictCode->get($j->properties['lccode'][0]->value);
            }

            if (isset($j->properties['propertyname']) ) {
                $j->properties['propertyname'][0]->value = $this->dictCode->get($j->properties['propertyname'][0]->value);
            }

            if (isset($j->properties['globalvar']) ) {
                $j->properties['globalvar'][0]->value = $this->dictCode->get($j->properties['globalvar'][0]->value);
            }

            $X = $this->json_encode($j);
            assert(!json_last_error(), $fileName . ' : error encoding normal ' . $j->label . ' : ' . json_last_error_msg() . "\n" . print_r($j, true));

            if (isset($this->tokenCounts[$j->label])) {
                ++$this->tokenCounts[$j->label];
            } else {
                $this->tokenCounts[$j->label] = 1;
            }
            ++$this->total;

            $this->driver->saveNodes($X);

            ++$total;
        }

        $this->totalLink += count($links);
        $this->links[] = $links;

        $datastore = exakat('datastore');
        $datastore->addRow('dictionary', $this->dictCode->getRecent());
    }

    private function fetchIds() {
        $b = hrtime(true);
        $res = $this->graphdb->query('g.V().as("id").as("eId").select("id", "eId").by(id).by("eId")');
        $this->ids = $res->toHash('eId', 'id');
        $e = hrtime(true);
        
        display("Loaded ".count($this->ids)." ids\n");

        assert($this->ids != array(), 'No ids were fetched from the server. This is not normal.');
    }

    private function saveNodeLinks(): void {
        if (empty($this->links)) {
            return;
        }

        if ($this->totalLink === 0) {
            return ;
        }

        // break down property files into small chunks for processing inside 300s.
        $links = array_merge(...$this->links);
        foreach ($links as &$link) {
            $link[1] = $this->ids[$link[1]];
            $link[2] = $this->ids[$link[2]];
        }
        unset($link);

        $this->driver->saveLinkGremlin($links);
    }

    private function json_encode(Stdclass $object): string {
        // in case the function name is full of non-encodable characters.
        if (isset($object->properties['fullnspath']) && !mb_check_encoding($object->properties['fullnspath'][0]->value, 'UTF-8')) {
            $object->properties['fullnspath'][0]->value = mb_convert_encoding($object->properties['fullnspath'][0]->value, 'UTF-8', 'ISO-8859-1');
        }
        if (isset($object->properties['propertyname']) && !mb_check_encoding((string) $object->properties['propertyname'][0]->value, 'UTF-8')) {
            $object->properties['propertyname'][0]->value = mb_convert_encoding($object->properties['propertyname'][0]->value,  'UTF-8', 'ISO-8859-1');
        }
        if (isset($object->properties['fullcode']) && !mb_check_encoding((string) $object->properties['fullcode'][0]->value, 'UTF-8')) {
            $object->properties['fullcode'][0]->value = mb_convert_encoding($object->properties['fullcode'][0]->value, 'UTF-8', 'ISO-8859-1');
        }
        if (isset($object->properties['code']) && !mb_check_encoding((string) $object->properties['code'][0]->value, 'UTF-8')) {
            $object->properties['code'][0]->value = mb_convert_encoding($object->properties['code'][0]->value, 'UTF-8', 'ISO-8859-1');
        }
        if (isset($object->properties['noDelimiter']) && !mb_check_encoding((string) $object->properties['noDelimiter'][0]->value, 'UTF-8')) {
            $object->properties['noDelimiter'][0]->value = mb_convert_encoding($object->properties['noDelimiter'][0]->value, 'UTF-8', 'ISO-8859-1');
        }
        if (isset($object->properties['delimiter']) && !mb_check_encoding((string) $object->properties['delimiter'][0]->value, 'UTF-8')) {
            $object->properties['delimiter'][0]->value = mb_convert_encoding($object->properties['delimiter'][0]->value, 'UTF-8', 'ISO-8859-1');
        }
        if (isset($object->properties['globalvar']) && !mb_check_encoding((string) $object->properties['globalvar'][0]->value, 'UTF-8')) {
            $object->properties['globalvar'][0]->value = mb_convert_encoding($object->properties['globalvar'][0]->value, 'UTF-8', 'ISO-8859-1');
        }
        if (isset($object->properties['ws']) && !mb_check_encoding((string) $object->properties['ws'][0]->value, 'UTF-8')) {
            $object->properties['ws'][0]->value = mb_convert_encoding($object->properties['ws'][0]->value, 'UTF-8', 'ISO-8859-1');
        }

        return json_encode($object);
    }

    private function log(string $message): void {
        $this->log->fwrite($message . PHP_EOL);
    }

    private function dropPropertyeId(): void {
        $query = 'g.V().has("eId").count()';
        $res = $this->graphdb->query($query);
        $times = ceil($res->toInt() / self::DROP_EID_CHUNK);

        $query = 'g.V().has("eId").limit(' . self::DROP_EID_CHUNK . ').properties("eId").drop()';
        for ($i = 0; $i < $times; ++$i) {
            $this->graphdb->query($query);
        }
    }
}

?>
