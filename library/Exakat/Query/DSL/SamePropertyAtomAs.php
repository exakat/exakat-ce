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
use Exakat\Exceptions\WrongNumberOfArguments;

class SamePropertyAtomAs extends DSL {
    public function run(): Command {
        switch (func_num_args()) {
            case 2:
                list($property, $name) = func_get_args();
                $caseSensitive = Analyzer::CASE_SENSITIVE;
                break;

            case 3:
                list($property, $name, $caseSensitive) = func_get_args();
                break;

            default:
                throw new WrongNumberOfArguments(__METHOD__, func_num_args(), 2);
        }

        if ($property !== SavePropertyAs::ATOM) {
            $this->assertProperty($property);
        }
        $this->assertVariable($name);

        if ($property === 'label') {
            return new Command('has("' . $property . '").filter{ it.get().label() == ' . $name . '}');
        } elseif ($property === 'id') {
            return new Command('has("' . $property . '").filter{ it.get().id() == ' . $name . '}');
        } elseif ($property === 'self') {
            assert(false, 'Dont use self with property');
        } elseif ($property === SavePropertyAs::ATOM) {
            return new Command('has("' . $property . '").filter{ it.get() == ' . $name . '}');
        } elseif (in_array($property, self::BOOLEAN_PROPERTY, \STRICT_COMPARISON)) {
            return new Command('has("' . $property . '").filter{ if ( it.get().properties("' . $property . '").any()) { ' . $name . ' == it.get().value("' . $property . '")} else {' . $name . ' == false; }; }');
        } elseif ($property === 'intval') {
            return new Command('has("' . $property . '").has("intval").filter{ it.get().value("intval") == ' . $name . '}');
        } elseif (in_array($property, array('reference'), \STRICT_COMPARISON) ) {
            return new Command('has("' . $property . '").filter{ if (it.get().properties("' . $property . '").any()) { ' . $name . ' == it.get().value("' . $property . '");} else { ' . $name . ' == false; }}');
        } elseif ($property === 'code' || $property === 'lccode') {
            if ($caseSensitive === Analyzer::CASE_SENSITIVE) {
                return new Command('has("' . $property . '").filter{ it.get().value("code") == ' . $name . '.value("code")}');
            } else {
                return new Command('has("' . $property . '").filter{ it.get().value("lccode") == ' . $name . '.value("lccode")}');
            }
        } elseif (in_array($property, self::INTEGER_PROPERTY, \STRICT_COMPARISON)) {
            return new Command('has("' . $property . '").filter{ it.get().value("' . $property . '") == ' . $name . '}');
        } else {
            $caseSensitive = $caseSensitive === Analyzer::CASE_SENSITIVE ? '' : '.toLowerCase()';

            return new Command('has("' . $property . '").filter{ it.get().value("' . $property . '")' . $caseSensitive . ' == ' . $name . $caseSensitive . '}');
        }
    }
}
?>
