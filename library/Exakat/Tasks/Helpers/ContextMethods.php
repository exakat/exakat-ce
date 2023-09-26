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


class ContextMethods {
    private array $currentMethods          = array();
    private array $currentMethodsCalls     = array();

    public function set(string $name, AtomInterface $atom): void {
        // A variable may be redefined later (case of $a = 1; fn($a) => $a;)
        $this->currentMethods[$name] = $atom;
    }

    public function listUndefinedMethods(): array {
        return array_diff(array_keys($this->currentMethodsCalls), array_keys($this->currentMethods));
    }

    public function getCurrentMethodsCalls(string $missing): array {
        return $this->currentMethodsCalls[$missing];
    }

    public function setCurrentMethodsCalls(string $missing, AtomInterface $atom): void {
        $this->currentMethodsCalls[$missing] = $atom;
    }

    public function addCall(string $name, AtomInterface $atom): void {
        array_collect_by($this->currentMethodsCalls, $name, $atom);
    }

    public function addMethod(string $name, AtomInterface $atom): void {
        $this->currentMethods[$name] = $atom;
    }
}

?>
