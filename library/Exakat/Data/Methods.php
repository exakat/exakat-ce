<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


namespace Exakat\Data;

use Exakat\Config;

class Methods {
    private $sqlite = null;
    private $phar_tmp = null;

    public const STRICT = true;
    public const LOOSE  = false;

    private const ARGS_COL = array('arg0', 'arg1', 'arg2', 'arg3', 'arg4', 'arg5', 'arg6', 'arg7', 'arg8', 'arg9', 'arg10', 'arg11');

    public function __construct(Config $config) {
        if ($config->is_phar) {
            $this->phar_tmp = tempnam(sys_get_temp_dir(), 'exMethods') . '.sqlite';
            copy($config->dir_root . '/data/methods.sqlite', $this->phar_tmp);
            $docPath = $this->phar_tmp;
        } else {
            $docPath = $config->dir_root . '/data/methods.sqlite';
        }
        $this->sqlite = new \Sqlite3($docPath, \SQLITE3_OPEN_READONLY);
    }

    public function __destruct() {
        if ($this->phar_tmp !== null && file_exists($this->phar_tmp)) {
            unlink($this->phar_tmp);
        }
    }

    public function getPhpFunctions(): array {
        $query = 'SELECT name FROM methods WHERE class = "PHP"';
        $res = $this->sqlite->query($query);
        $return = array();

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row;
        }

        return $return;
    }

    public function getPhpClasses(): array {
        $query = 'SELECT DISTINCT class FROM methods WHERE class != "PHP"';
        $res = $this->sqlite->query($query);
        $return = array();

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row;
        }

        return $return;
    }

    public function getMethodsArgsInterval(): array {
        $query = 'SELECT class, name, args_min, args_max FROM methods WHERE class != "PHP"';
        $res = $this->sqlite->query($query);
        $return = array();

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row;
        }

        return $return;
    }

    public function getFunctionsArgsInterval(): array {
        $query = 'SELECT class, name, args_min, args_max FROM methods WHERE Class = "PHP"';
        $res = $this->sqlite->query($query);
        $return = array();

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row;
        }

        return $return;
    }

    public function getFunctionsLastArgsNotBoolean(): array {
        $clauses = array();
        foreach(self::ARGS_COL as $position => $name) {
            $max = $position + 1;
            $clauses[] = "(args_max = $max AND not instr(arg$position, 'bool') AND arg$position != '')";
        }

        $query = 'SELECT \'\' || lower(methods.name) AS fullnspath, args_max - 1 AS position FROM methods 
JOIN args_type ON args_type.name = methods.name
WHERE methods.class = "PHP" AND
     ' . implode(' OR ', $clauses);
        $res = $this->sqlite->query($query);
        $return = array();

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row['fullnspath'];
        }

        return $return;
    }

    public function getFunctionsReferenceArgs(): array {
        $clauses = array();
        foreach(self::ARGS_COL as $position => $name) {
            $clauses[] = "SELECT name AS function, $position AS position FROM args_is_ref WHERE Class = 'PHP' AND arg$position = 'reference'";
        }

        $query = implode(' UNION ', $clauses);

        $res = $this->sqlite->query($query);
        $return = array();

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row;
        }

        return $return;
    }

    public function getFunctionsValueArgs(): array {
        $clauses = array();
        foreach(self::ARGS_COL as $position => $name) {
            $clauses[] = "SELECT name AS function, $position AS position FROM args_is_ref WHERE Class = 'PHP' AND arg$position = 'value'";
        }

        $query = implode(' UNION ', $clauses);

        $res = $this->sqlite->query($query);
        $return = array();

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row;
        }

        return $return;
    }

    public function getDeterministFunctions(): array {
        $query = 'SELECT name FROM methods WHERE determinist = 1';
        $res = $this->sqlite->query($query);
        $return = array();

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row['name'];
        }

        return $return;
    }

    public function getNonDeterministFunctions(): array {
        $query = 'SELECT name FROM methods WHERE determinist = 0';
        $res = $this->sqlite->query($query);
        $return = array();

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row['name'];
        }

        return $return;
    }

    public function getInternalParameterType(): array {
        $return = array();

        $args = self::ARGS_COL;
        foreach($args as $id => $arg) {
            $query = <<<SQL
SELECT $arg, lower(GROUP_CONCAT('\' || name)) AS functions FROM args_type WHERE class='PHP' AND $arg IN ('int', 'array', 'bool','string') GROUP BY $arg
SQL;
            $res = $this->sqlite->query($query);

            $position = array();
            while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
                $position[$row[$arg]] = explode(',', $row['functions']);
            }

            $return[$id] = $position;
        }

        return $return;
    }

    public function getFunctionsByArgType(string $typehint = 'int', $strict = self::STRICT): array {
        $return = array_fill(0, 10, array());

        if ($strict === self::LOOSE) {
            $search = " LIKE '%$typehint%'";
        } elseif ($strict === self::STRICT) {
            $search = " = '$typehint'";
        } else {
            // Default is strict
            $search = " = '$typehint'";
        }

        $clauses = array();
        foreach(self::ARGS_COL as $position => $name) {
            $max = $position + 1;
            $clauses[] = "SELECT name AS function, $position AS position FROM args_type WHERE Class = 'PHP' AND arg$position $search";
        }

        $query = implode(' UNION ', $clauses);

        $res = $this->sqlite->query($query);

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            array_collect_by($return, (int) $row['position'], '\\' . mb_strtolower($row['function']));
        }

        return $return;
    }

    public function getBugFixes(): array {
        $return = array();

        $query = <<<'SQL'
SELECT * FROM bugfixes ORDER BY SUBSTR(solvedIn72, 5) + 0 DESC, SUBSTR(solvedIn71, 5) + 0 DESC, SUBSTR(solvedIn70, 5) + 0 DESC, SUBSTR(56, 5) + 0 DESC 
SQL;
        $res = $this->sqlite->query($query);

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = $row;
        }

        return $return;
    }

    public function getFunctionsByReturn(bool $singleTypeOnly = self::LOOSE): array {
        $return = array();

        if ($singleTypeOnly === true) {
            $where = ' AND return NOT LIKE "%,%"';
        } else {
            $where = '';
        }

        $query = <<<SQL
SELECT return, lower(GROUP_CONCAT('\' || name)) AS functions 
    FROM args_type 
    WHERE class='PHP'         AND 
          return IS NOT NULL $where
    GROUP BY return
SQL;
        $res = $this->sqlite->query($query);

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $types = explode(',', $row['return']);
            foreach($types as $type) {
                array_collect_by($return, $type, explode(',', $row['functions']));
            }
        }

        foreach($return as &$list) {
            $list = array_merge(...$list);
        }

        return $return;
    }

    public function getFunctionsByReturnType(string $type = 'int', bool $singleTypeOnly = self::STRICT): array {
        $return = array();

        if ($singleTypeOnly === self::STRICT) {
            $where = ' AND return NOT LIKE "%,%"';
        } else {
            $where = '';
        }

        $query = <<<SQL
SELECT return, lower(GROUP_CONCAT('\' || name)) AS functions 
    FROM args_type 
    WHERE class='PHP'         AND 
          return LIKE '%$type%' AND
          return IS NOT NULL $where
    GROUP BY return
SQL;
        $res = $this->sqlite->query($query);

        while($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            $return[] = explode(',', $row['functions']);
        }

        $return = array_merge(...$return);

        return $return;
    }
}

?>
