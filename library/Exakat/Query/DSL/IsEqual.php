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


class IsEqual extends DSL {
    public function run(): Command {
        switch (func_num_args()) {
            case 2:
                list($value1, $value2) = func_get_args();

                // @todo : this only works with variables and it should also go away
                $this->assertVariable($value1);
                $this->assertVariable($value2);

                return new Command("filter{ $value1 == $value2; }");

            case 1:
                list($value1) = func_get_args();

                return $this->makeCommand($value1);

            default:
                assert(false, 'Wrong number of argument for ' . __METHOD__ . '. 2 or 1 are expected, ' . func_num_args() . ' provided');
        }
    }

    private function makeCommand(int|string $value): Command {
        // It is an integer
        if (is_int($value)) {
            return new Command('is(eq(' . (string) $value . '))');
        }

        // It is a gremlin variable
        if ($this->isVariable($value)) {
            return new Command('is(eq(' . $value . '))');
        }

        // It is a label
        if ($this->isLabel($value)) {
            return new Command('where(eq("' . $value . '"))');
        }

        // It is a gremlin property
        if ($this->isProperty($value)) {
            assert(false, 'This is not supported with properties yet');
        }

        assert(false, $value . ' must be an integer, label, atom property or gremlin variable in ' . __METHOD__);
    }
}
?>
