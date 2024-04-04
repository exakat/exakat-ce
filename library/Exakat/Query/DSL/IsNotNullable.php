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

class IsNotNullable extends DSL {
    public function run(): Command {
        switch (func_num_args()) {
            case 1:
                list($nullable) = func_get_args();
                $nullable = in_array($nullable, array(IsNullable::EXPLICIT, IsNullable::IMPLICIT), \STRICT_COMPARISON) ? $nullable : IsNullable::IMPLICIT;
                break;

            case 0:
                $nullable = IsNullable::IMPLICIT;
                break;

            default:
                assert(false, 'Wrong number of argument for ' . __METHOD__ . '. 1 is expected, ' . func_num_args() . ' provided');
        }

        if ($nullable === IsNullable::IMPLICIT) {
            return new Command('not(where( __.out("RETURNTYPE", "TYPEHINT").or(
                __.hasLabel("Scalartypehint").has("fullnspath", "\\\\null"),
                __.hasLabel("Null")
                )))');
        } else {
            return new Command('not(where( __.out("RETURNTYPE", "TYPEHINT").hasLabel("Scalartypehint").has("fullnspath", "\\\\null") ) )');
        }
    }
}
?>
