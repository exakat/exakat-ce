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


class NotSameVariableAs extends DSL {
    public function run(): Command {
        if (func_num_args() !== 2) {
            throw new QueryException('Wrong number of arguments for ' . __METHOD__);
        }

        list($variable, $values) = func_get_args();

        $this->assertVariable($variable);

        if (is_array($values)) {
            return new Command("filter{ !($variable in ***) }", array($values));
        } elseif ($this->isVariable($values)) {
            return new Command("filter{ $variable != $values }");
        } elseif (is_string($values)) {
            return new Command("filter{ $variable != *** }", array($values));
        } else {
            die('Unsupported type of ' . gettype($values));
        }
    }
}
?>
