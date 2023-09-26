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

abstract class Fileset {
    protected ?Fileset $filter  = null;
    protected array    $files   = array();
    protected array    $ignored = array();

    public function addFilter(Fileset $filter): void {
        if ($this->filter === null) {
            $this->filter = $filter;
            $this->filter->setFiles($this->files);
        } else {
            $this->filter->addFilter($filter);
        }
    }

    public function getFiles(?array $files = null): array {
        if ($this->filter === null) {
            return $this->files;
        } else {
            return $this->filter->getFiles();
        }
    }

    public function getIgnored(): array {
        if ($this->filter === null) {
            return $this->ignored;
        } else {
            return $this->filter->getIgnored();
        }
    }

    abstract public function setFiles(array $files): void;
}

?>
