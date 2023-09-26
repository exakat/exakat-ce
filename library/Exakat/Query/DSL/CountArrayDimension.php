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

class CountArrayDimension extends DSL {
    public const UP = 1;
    public const DOWN = 2;

    public function run(): Command {
        switch (func_num_args()) {
            case 2:
                list($variable, $order) = func_get_args();
                assert( in_array($order, array(self::UP, self::DOWN)), '');
                break;

            default:
                list($variable) = func_get_args();
                $order = self::DOWN;
                break;
        }


        $this->assertVariable($variable, self::VARIABLE_WRITE);

        if ($order === self::UP) {
            $command = new Command('sideEffect{ ' . $variable . ' = 0; }
.repeat( __.in("VARIABLE", "APPEND").hasLabel("Array", "Arrayappend").sideEffect{  ' . $variable . ' =  ' . $variable . ' + 1;}).until( __.not(__.in("VARIABLE", "APPEND")))');
        } elseif ($order === self::DOWN) {
            $command = new Command('sideEffect{ ' . $variable . ' = 0; }
.repeat( __.out("VARIABLE", "APPEND").sideEffect{' . $variable . ' =  ' . $variable . ' + 1;}).until(__.not(hasLabel("Array", "Arrayappend")))'
            );
        }

        return $command;
    }
}
?>
