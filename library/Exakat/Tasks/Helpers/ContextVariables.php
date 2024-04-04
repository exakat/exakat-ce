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

namespace Exakat\Tasks\Helpers;


class ContextVariables {
    private $list = array();

    public function set(string $name, AtomInterface $atom): void {
        // A variable may be redefined later (case of $a = 1; fn($a) => $a;)
        $this->list[$name] = $atom;
    }

    public function get(string $name): AtomInterface {
        assert(isset($this->list[$name]), "Get a undefined variable $name");
        return $this->list[$name];
    }

    public function exists(string $name): bool {
        return isset($this->list[$name]);
    }

    public function list(): array {
        return $this->list;
    }
}

?>
