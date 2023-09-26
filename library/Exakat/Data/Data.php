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


namespace Exakat\Data;

use Sqlite3;
use Exakat\Config;

abstract class Data {
    private Config $config;

    protected string $name = '';

    private Sqlite3 $sqlite;
    private string  $phar_tmp = null;

    public function __construct(string $name) {
        $this->name = $name;
        $this->config = exakat('config');

        $fullpath = $this->config->dir_root . '/data/' . $name . '.sqlite';
        if ($this->config->is_phar) {
            $this->phar_tmp = tempnam(sys_get_temp_dir(), $name) . '.sqlite';
            if (file_exists($fullpath)) {
                copy($fullpath, $this->phar_tmp);
            } elseif (($this->config->ext !== null) && $this->config->ext->fileExists("data/$name.sqlite") ) {
                $this->config->ext->copyFile("data/$name.sqlite", $this->phar_tmp);
            } else {
                assert(false, "No database for '$name.sqlite'.");
            }
            $docPath = $this->phar_tmp;
        } elseif (file_exists($fullpath)) {
            $docPath = $fullpath;
        } elseif (($this->config->ext !== null) && $this->config->ext->fileExists("data/$name.sqlite") ) {
            $this->phar_tmp = tempnam(sys_get_temp_dir(), $name) . '.sqlite';
            $this->config->ext->copyFile("data/$name.sqlite", $this->phar_tmp);
            $docPath = $this->phar_tmp;
        } else {
            assert(false, "No database for '$name.sqlite'.");
        }
        $this->sqlite = new Sqlite3($docPath, \SQLITE3_OPEN_READONLY);
    }

    public function __destruct() {
        if ($this->phar_tmp !== null) {
            unlink($this->phar_tmp);
        }
    }

    public function getVersions(string $component = null): array {
        // @todo : $component is not used. Is could have been the namespace
        $query = 'SELECT version AS version FROM versions ORDER BY 1';
        $res = $this->sqlite->query($query);

        $return = array();
        while ($row = $res->fetchArray(\SQLITE3_NUM)) {
            $return[] = $row[0];
        }

        return $return;
    }

    public function getCIT(string $component, string $version = null): array {
        // @todo : $component is not used. Is could have been the namespace
        $query = 'SELECT namespaces.name || "\" || cit.name AS className, version FROM cit 
                    JOIN namespaces 
                      ON cit.namespaceId = namespaces.id
                    JOIN versions 
                      ON namespaces.versionId = versions.id ';
        if ($version !== null) {
            $query .= " WHERE versions.version = \"$version\"";
        }

        $res = $this->sqlite->query($query);

        $return = array();
        while ($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            if (isset($return[$row['version']])) {
                $return[$row['version']][] = $row['className'];
            } else {
                $return[$row['version']] = array($row['className']);
            }
        }

        return $return;
    }

    public function getClasses(string $component, string $version = null): array {
        // @todo : $component is not used. Is could have been the namespace
        $query = 'SELECT namespaces.name || "\" || cit.name AS className, version FROM cit 
                    JOIN namespaces 
                      ON cit.namespaceId = namespaces.id
                    JOIN versions 
                      ON namespaces.versionId = versions.id 
                    WHERE cit.type = "class"';
        if ($version !== null) {
            $query .= " AND versions.version = \"$version\"";
        }

        $res = $this->sqlite->query($query);

        $return = array();
        while ($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            if (isset($return[$row['version']])) {
                $return[$row['version']][] = $row['className'];
            } else {
                $return[$row['version']] = array($row['className']);
            }
        }

        return $return;
    }

    public function getInterfaces(string $component, version $version = null): array {
        // @todo : $component is not used. Is could have been the namespace
        $query = 'SELECT namespaces.name || "\" || cit.name AS interfaceName, version FROM cit 
                    JOIN namespaces 
                      ON cit.namespaceId = namespaces.id
                    JOIN versions 
                      ON namespaces.versionId = versions.id 
                    WHERE cit.type = "interface"';
        if ($version !== null) {
            $query .= " AND versions.version = \"$version\"";
        }

        $res = $this->sqlite->query($query);
        $return = array();

        while ($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            if (isset($return[$row['version']])) {
                $return[$row['version']][] = $row['interfaceName'];
            } else {
                $return[$row['version']] = array($row['interfaceName']);
            }
        }

        return $return;
    }

    public function getTraits(string $component, string $version = null): array {
        // @todo : $component is not used. Is could have been the namespace
        $query = 'SELECT namespaces.name || "\" || cit.name AS traitName, version FROM cit 
                    JOIN namespaces 
                      ON cit.namespaceId = namespaces.id
                    JOIN versions 
                      ON namespaces.versionId = versions.id 
                    WHERE cit.type = "trait" AND
                          namespaces.name != "" ';
        if ($version !== null) {
            $query .= " AND versions.version = \"$version\"";
        }

        $res = $this->sqlite->query($query);
        $return = array();

        while ($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            if (isset($return[$row['version']])) {
                $return[$row['version']][] = $row['traitName'];
            } else {
                $return[$row['version']] = array($row['traitName']);
            }
        }

        return $return;
    }

    public function getNamespaces(string $component, string $version = null): array {
        // @todo : $component is not used. Is could have been the namespace
        $query = 'SELECT namespaces.name as namespaceName, version FROM namespaces 
                    JOIN versions 
                      ON namespaces.versionId = versions.id 
                  WHERE namespaces.name != "" ';
        if ($version !== null) {
            $query .= " AND versions.version = \"$version\"";
        }

        $res = $this->sqlite->query($query);
        $return = array();

        while ($row = $res->fetchArray(\SQLITE3_ASSOC)) {
            if (isset($return[$row['version']])) {
                $return[$row['version']][] = $row['namespaceName'];
            } else {
                $return[$row['version']] = array($row['namespaceName']);
            }
        }

        return $return;
    }
}

?>
