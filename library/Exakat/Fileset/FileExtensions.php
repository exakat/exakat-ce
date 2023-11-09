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

class FileExtensions extends Fileset {
    private array $extensions   = array();

    public function __construct(array $extensions) {
        // @todo : checks the content of this array before usage. It should be only strings, and short ones.
        $this->extensions = $extensions;
    }

    public function setFiles(array $files): void {
        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array(mb_strtolower($ext), $this->extensions)) {
                $this->files[] = $file;
            } else {
                $this->ignored[$file] = "Ignored extension ($ext)";
            }
        }
    }

    public function filterFile(array $result): bool {
        $this->files = array();
        $this->setFiles(array($result['file']));

        return !empty($this->files);
    }
}

?>
