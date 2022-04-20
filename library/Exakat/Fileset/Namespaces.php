<?php declare(strict_types = 1);
/*
 * Copyright 2012-2021 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


namespace Exakat\Fileset;

class Namespaces extends Fileset {
    private array $namespaces    = array();

    public function __construct(array $namespaces) {
        foreach(array_filter($namespaces) as $namespace) {
            if ($namespace[0] === '\\') {
                $this->namespaces[] = mb_strtolower("$namespace*");
            } else {
                $this->namespaces[] = mb_strtolower("*$namespace*");
            }
        }
    }

    public function setFiles(array $files) {
        // No feature here, as namespaces can only be filtered after load

        // Nothing to do, just pass the files to the next
        $this->files = $files;
        $this->ignoredFiles = array();
    }

    public function filterFile(array $result): bool {
        $namespace = $result['namespace'];

        foreach($this->namespaces as $n) {
            if (fnmatch($n, $namespace, FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }
}

?>
