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

namespace Exakat\Tasks\Helpers;


class ContextProperties {
    private array $definitions  = array();
    private array $calls        = array();

    public function listUndefinedProperties(): array {
        return array_diff(array_keys($this->calls), array_keys($this->definitions));
    }

    public function getCurrentPropertyCalls(string $missing): array {
        return $this->calls[$missing];
    }

    public function setCurrentPropertyCalls(string $missing, AtomInterface $atom): void {
        $this->calls[$missing] = $atom;
    }

    public function addCall(string $name, AtomInterface $atom): void {
        array_collect_by($this->calls, $name, $atom);
    }

    public function addProperty(string $name, AtomInterface $atom): void {
        $this->definitions[$name] = $atom;
    }
}

?>
