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


namespace Exakat\Fileset;

class IncludeDirs extends Fileset {
    private array $includeDirs   = array();

    public function __construct(array $includeDirs) {
        foreach (array_filter($includeDirs) as $include) {
            if ($include[0] === '/') {
                $this->includeDirs[] = "$include*";
            } else {
                $this->includeDirs[] = "*$include*";
            }
        }
    }

    public function setFiles(array $files): void {
        foreach ($files as $file) {
            $found = false;
            foreach ($this->includeDirs as $ignore) {
                $found = $found || fnmatch($ignore, $file);
            }

            if ($found) {
                $this->ignored[$file] = "include_dir file ($file)";
            } else {
                $this->files[] = $file;
            }
        }
    }
}

?>
