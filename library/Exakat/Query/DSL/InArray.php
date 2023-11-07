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

use Exakat\Analyzer\Analyzer;

class InArray extends DSL {
    public function run(): Command {
        switch (func_num_args()) {
            case 2:
                list($name, $value) = func_get_args();
                $case = Analyzer::CASE_SENSITIVE;
                break;

            case 3:
                list($name, $value, $case) = func_get_args();
                if ($case !== Analyzer::CASE_SENSITIVE && $case !== Analyzer::CASE_INSENSITIVE) {
                    assert(true, 'Third argument must be case sensitivity.');
                }
                break;

            default:
                assert(false, 'Wrong number of argument for ' . __METHOD__ . '. 2 or 3 are expected, ' . func_num_args() . ' provided');
        }

        assert(is_array($value), '2nd argument of ' . __METHOD__ . ' must be an array');
        $value = array_values($value);

        if ($this->isProperty($name)) {
            if ($case === Analyzer::CASE_SENSITIVE) {
                return new Command('has("' . $name . '", within(***))', array($value));
            } else {
                return new Command('has("' . $name . '").filter{ it.get().value("' . $name . '").toLowerCase() in ***}', array($value));
            }
        }

        if ($this->isVariable($name)) {
            if ($case === Analyzer::CASE_SENSITIVE) {
                return new Command('filter{' . $name . ' in ***}', array($value));
            } else {
                return new Command('filter{ ' . $name . '.toLowerCase() in ***}', array($value));
            }
        }

        assert(false, 'Not a property nor a variable in ' . __METHOD__);
    }
}
?>
