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

class Set extends Fileset {
    private array $filesets   = array();

    public function __construct(array $filesets) {
        foreach($filesets as $fileset) {
            $this->filesets[$fileset] = array('/a/fooA.php');
        }
    }

    public function setFiles(array $files) {
        $this->files = array_intersect(array_merge(...array_values($this->filesets)), $files);
        $this->ignored = array_diff($files, $this->files);
    }

    public function setFiles(array $files) {
        foreach($files as $file) {
            $f = basename($file);
            if (isset($this->names[mb_strtolower($f)])) {
                $this->ignored[$file] = "Ignored file ($file)";
            } else {
                $this->files[] = $file;
            }
        }
    }
}

?>
