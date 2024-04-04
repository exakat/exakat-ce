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

namespace Exakat\Tasks\Helpers;

use const STRICT_COMPARISON;
use Sqlite3;

class Calls {
    public const A_CLASS        = 'class';
    public const FUNCTION       = 'function';
    public const CONST          = 'const';
    public const STATICCONSTANT = 'staticconstant';
    public const STATICMETHOD   = 'staticmethod';
    public const STATICPROPERTY = 'staticproperty';
    public const METHOD         = 'method';
    public const PROPERTY       = 'property';
    public const GOTO           = 'goto';

    public const ALL = array(self::A_CLASS,
                        self::FUNCTION,
                        self::CONST,
                        self::STATICCONSTANT,
                        self::STATICMETHOD,
                        self::STATICPROPERTY,
                        self::METHOD,
                        self::PROPERTY,
                        self::GOTO,
                       );

    private Sqlite3 $callsSqlite;

    private array $definitions = array();
    private array $calls       = array();

    public function __construct(Sqlite3 $sqlite) {
        $this->callsSqlite = $sqlite;

        $calls = <<<'SQL'
CREATE TABLE IF NOT EXISTS calls (
    type STRING,
    fullnspath STRING,
    globalpath STRING,
    atom STRING,
    id INTEGER
)
SQL;
        $this->callsSqlite->query($calls);

        $definitions = <<<'SQL'
CREATE TABLE IF NOT EXISTS definitions (
    type STRING,
    fullnspath STRING,
    globalpath STRING,
    atom STRING,
    id INTEGER
)
SQL;
        $this->callsSqlite->query($definitions);
    }

    public function reset(): void {
        $this->calls       = array();
        $this->definitions = array();
    }

    public function save(): void {
        if (!empty($this->calls)) {
            $chunks = array_chunk($this->calls, SQLITE_CHUNK_SIZE);
            foreach ($chunks as $chunk) {
                $query = 'INSERT INTO calls VALUES ' . implode(', ', $chunk);
                $this->callsSqlite->query($query);
            }
            $this->calls = array();
        }

        if (!empty($this->definitions)) {
            $chunks = array_chunk($this->definitions, SQLITE_CHUNK_SIZE);
            foreach ($chunks as $chunk) {
                $query = 'INSERT INTO definitions VALUES ' . implode(', ', $chunk);
                $this->callsSqlite->query($query);
            }
            $this->definitions = array();
        }
    }

    public function addCall(string $type, string $fullnspath, AtomInterface $call): void {
        assert(in_array($type, self::ALL, STRICT_COMPARISON), "Unknown call type : $type\n");

        if (empty($fullnspath)) {
            return;
        }

        // No need for This
        if (in_array($call->atom, array('Parent',
                                        'Isset',
                                        'List',
                                        'Empty',
                                        'Eval',
                                        'Exit',
                                        ), STRICT_COMPARISON)) {
            return;
        }

        if ($type === 'class') {
            $globalpath = $fullnspath;
        } elseif ($call->absolute === true) {
            $globalpath = $fullnspath;
        } else {
            $globalpath = $this->makeGlobalPath($fullnspath);
        }

        $fullnspath = Sqlite3::escapeString($fullnspath);
        $globalpath = Sqlite3::escapeString($globalpath);

        $this->calls[] = "('{$type}',
                           '{$fullnspath}',
                           '{$globalpath}',
                           '{$call->atom}',
                           '{$call->id}')";
    }

    public function addNoDelimiterCall(AtomInterface $call): void {
        if (empty($call->noDelimiter)) {
            return; // Can't be a class anyway.
        }
        if ((int) $call->noDelimiter !== 0) {
            return; // Can't be a class anyway.
        }
        // single : is OK
        // \ is OK (for hardcoded path)
        if (preg_match_all('/[$ #?;%^\*\'\"\. <>~&,|\(\){}\[\]\/\s=\+!`@\-]/is', $call->noDelimiter)) {
            return; // Can't be a class anyway.
        }

        if (str_contains($call->noDelimiter, '::')) {
            $fullnspath = mb_strtolower($call->noDelimiter);

            if (empty($fullnspath)) {
                return;
            } elseif ($fullnspath[0] === ':') {
                return;
            }

            if ($fullnspath[0] !== '\\') {
                $fullnspath = '\\' . $fullnspath;
            }

            $types = array('staticmethod', 'staticconstant');
        } else {
            $types = array('function', 'class');

            $fullnspath = mb_strtolower($call->noDelimiter);
            if (empty($fullnspath) || $fullnspath[0] !== '\\') {
                $fullnspath = '\\' . $fullnspath;
            }
            if (str_contains($fullnspath, '\\\\')  ) {
                $fullnspath = stripslashes($fullnspath);
            }
        }

        $atom = 'String';

        foreach ($types as $type) {
            $globalpath = $this->makeGlobalPath($fullnspath);

            $quotedFullnspath = Sqlite3::escapeString($fullnspath);
            $quotedGlobalpath = Sqlite3::escapeString($globalpath);
            $this->calls[] = "('$type',
                               '{$quotedFullnspath}',
                               '{$quotedGlobalpath}',
                               '{$atom}',
                               '{$call->id}')";
        }
    }

    public function addDefinition(string $type, string $fullnspath, AtomInterface $definition): void {
        assert(in_array($type, self::ALL, STRICT_COMPARISON), "Unknown definition type : $type\n");
        if (empty($fullnspath)) {
            return;
        }

        $globalpath = $this->makeGlobalPath($fullnspath);

        $quotedFullnspath = Sqlite3::escapeString($fullnspath);
        $quotedGlobalpath = Sqlite3::escapeString($globalpath);
        $this->definitions[] = "('{$type}',
                                 '{$quotedFullnspath}',
                                 '{$quotedGlobalpath}',
                                 '{$definition->atom}',
                                 '{$definition->id}')";
    }

    private function makeGlobalPath(string $fullnspath): string {
        if ($fullnspath === 'undefined') {
            $globalpath = '';
        } elseif (preg_match('/(\\\\[^\\\\]+)$/', $fullnspath, $r)) {
            $globalpath = $r[1];
        } else {
            $globalpath = substr($fullnspath, (int) strrpos($fullnspath, '\\'));
        }

        return $globalpath;
    }
}

?>
