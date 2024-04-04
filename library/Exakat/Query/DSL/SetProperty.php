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


namespace Exakat\Query\DSL;

use Exakat\Exceptions\QueryException;

class SetProperty extends DSL {
    public function run(): Command {
        switch(func_num_args()) {
            case 2:
                list($property, $value) = func_get_args();
                break;

            case 3:
                list($property, $value, $valueProperty) = func_get_args();
                break;

            default:
                throw new QueryException('Wrong number of argument for ' . __METHOD__ . '. 2 or 3 are expected, ' . func_num_args() . ' provided');
        }

        $this->assertProperty($property);
        if ($value === true) {
            return new Command("property(\"$property\", true)");
        } elseif ($value === false) {
            return new Command("property(\"$property\", false)");
        } elseif (is_int($value)) {
            return new Command("property(\"$property\", $value)");
        } elseif ($this->isVariable($value)) {
            if (isset($valueProperty)) {
                $this->assertProperty($valueProperty);
                if (in_array($valueProperty, array('isPhp', 'isExt', 'isStub'), STRICT_COMPARISON)) {
                    return new Command("sideEffect{ if (definition.properties(\"$valueProperty\").any())  { it.get().property(\"$valueProperty\", true); } }");
                } else {
                    return new Command("sideEffect{ it.get().property(\"$property\", $value.value(\"$valueProperty\")); }");
                }
            } else {
                return new Command("sideEffect{ it.get().property(\"$property\", $value); }");
            }
        } else {
            // This is a value
            return new Command("sideEffect{ it.get().property(\"$property\", ***); }", array($value));
        }
    }
}
?>
