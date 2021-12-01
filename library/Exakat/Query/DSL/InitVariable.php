<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


class InitVariable extends DSL {
    public const TYPE_CODE = 2;
    public const TYPE_ARGUMENT = 3;

    public function run(): Command {
        if (func_num_args() === 3) {
            list($name, $value, $type) = func_get_args();
            if (!in_array($type, array(self::TYPE_CODE, self::TYPE_ARGUMENT))) {
                $type = self::TYPE_ARGUMENT;
            }
        } elseif (func_num_args() === 2) {
            list($name, $value) = func_get_args();
            $type = self::TYPE_CODE;
        } else {
            list($name) = func_get_args();
            $value = '[]';
            $type = self::TYPE_CODE;
        }

        $this->assertVariable($name, self::VARIABLE_WRITE);

        // Here, there is one name for the variable
        if ($type === self::TYPE_ARGUMENT) {
            return new Command('sideEffect{ ' . $name . ' = *** }', array($value));
        }

        // Here, there is one name for the variable
        if ($type === self::TYPE_CODE) {
            return new Command('sideEffect{ ' . $name . ' = ' . $value . ' }');
        }

        assert(false, 'Wrong format for ' . __METHOD__ . '. Either string/value or array()/array()');
    }
}
?>
