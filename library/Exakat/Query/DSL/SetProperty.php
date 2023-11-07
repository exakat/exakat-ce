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


namespace Exakat\Query\DSL;

class SetProperty extends DSL {
    public function run(): Command {
        list($property, $value) = func_get_args();

        $this->assertProperty($property);
        if ($value === true) {
            return new Command("property(\"$property\", true)");
        } elseif ($value === false) {
            return new Command("property(\"$property\", false)");
        } elseif (is_int($value)) {
            return new Command("property(\"$property\", $value)");
        } elseif ($this->isVariable($value)) {
            return new Command("sideEffect{ it.get().property(\"$property\", $value); }");
        } else {
            // This is a value
            return new Command("sideEffect{ it.get().property(\"$property\", ***); }", array($value));
        }
    }
}
?>
