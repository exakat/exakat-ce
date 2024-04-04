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

namespace Exakat\Analyzer\Dump;

use Exakat\Dump\Dump;

abstract class AnalyzerTable extends AnalyzerDump {
    // Dumps are made of 2 queries : create table and some inserts.
    private const MIN_NUMBER_OF_QUERY = 2;

    protected int $storageType = self::QUERY_TABLE;

    protected array $dumpQueries = array();

    public function prepareDirectQuery(string $query): void {
        ++$this->queryId;

        $result = $this->gremlin->query($query);

        if ($result->isEmpty()) {
            return ;
        }

        ++$this->queryCount;

        array_unshift($this->dumpQueries, $this->analyzerSQLTable);
        array_unshift($this->dumpQueries, "DROP TABLE IF EXISTS {$this->analyzerTable}");

        $c = $result->toArray();

        $this->processedCount += count($c);
        $this->rowCount       += count($c);

        $valuesSQL = array();
        foreach ($c as $row) {
            $valuesSQL[] = "(NULL, '" . implode("', '", array_map(array('\\Sqlite3', 'escapeString'), $row)) . "') \n";
        }

        $chunks = array_chunk($valuesSQL, SQLITE_CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            $query = 'INSERT INTO ' . $this->analyzerTable . ' VALUES ' . implode(', ', $chunk);
            $this->dumpQueries[] = $query;
        }
    }

    public function prepareQuery(): void {
        ++$this->queryId;

        $result = $this->rawQuery();

        ++$this->queryCount;

        array_unshift($this->dumpQueries, $this->analyzerSQLTable);
        array_unshift($this->dumpQueries, "DROP TABLE IF EXISTS {$this->analyzerTable}");

        if ($result->isEmpty()) {
            return ;
        }

        $c = $result->toArray();

        $this->processedCount += count($c);
        $this->rowCount       += count($c);

        $valuesSQL = array();
        foreach ($c as $row) {
            // Possible NULL in row here.
            $valuesSQL[] = "(NULL, '" . implode("', '", array_map(array('\\Sqlite3', 'escapeString'), $row)) . "') \n";
        }

        $chunks = array_chunk($valuesSQL, SQLITE_CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            $query = 'INSERT INTO ' . $this->analyzerTable . ' VALUES ' . implode(', ', $chunk);
            $this->dumpQueries[] = $query;
        }
    }

    public function execQuery(): int {
        assert($this->analyzerTable !== 'no analyzer table name', 'No table name for ' . static::class);
        assert($this->analyzerSQLTable !== 'no analyzer sql creation', 'No table name for ' . static::class);

        if (count($this->dumpQueries) >= self::MIN_NUMBER_OF_QUERY) {
            $this->prepareForDump($this->dumpQueries);
        }

        $this->dumpQueries = array();

        return 0;
    }

    public function getDump(): array {
        $dump      = Dump::factory($this->config->dump);

        $res = $dump->fetchTable($this->analyzerTable);
        return $res->toArray();
    }
}

?>
