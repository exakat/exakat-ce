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

class IgnoreDirs extends Fileset {
    private array $ignoreDirs    = array();
    private array $includeDirs   = array();

    public function __construct(array $ignoreDirs, array $includeDirs) {
        foreach (array_filter($ignoreDirs) as $ignore) {
            if ($ignore[0] === '/') {
                $this->ignoreDirs[] = "$ignore*";
            } else {
                $this->ignoreDirs[] = "*$ignore*";
            }
        }

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
            foreach ($this->ignoreDirs as $ignore) {
                if (fnmatch($ignore, $file)) {
                    $found = true;
                    break 1;
                }
            }

            if ($found) {
                foreach ($this->includeDirs as $include) {
                    if (fnmatch($include, $file)) {
                        $found = false;
                        break 1;
                    }
                }
            }

            if ($found) {
                $this->ignored[$file] = "ignore_dirs file ($file)";
            } else {
                $this->files[] = $file;
            }
        }
    }

    public function filterFile(array $result): bool {
        $file = $result['file'];
        $this->files = array();
        $this->setFiles(array($file));

        return !empty($this->files);
    }
}

?>
