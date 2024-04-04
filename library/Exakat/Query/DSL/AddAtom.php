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


class AddAtom extends DSL {
    public function run(): Command {
        $this->assertArguments(2, func_num_args(), __METHOD__);
        list($atom, $properties) = func_get_args();

        $return = new Command('addV("' . $atom . '")');

        foreach ($properties as $name => $value) {
            if (is_string($value)) {
                if ($this->isVariable($value)) {
                    // Nothing
                } elseif (substr($value, 0, 6) === 'select') {
                    // Nothing
                } elseif (substr($value, 0, 3) === '__.') {
                    // Nothing
                } else {
                    $value = $this->protectValue($value);
                }
            } elseif (is_bool($value)) {
                $value = json_encode($value);
            } elseif (is_int($value)) {
                $value = (string) $value;
            } else {
                assert(false, 'Wrong type for value : ' . gettype($value));
            }

            $sideEffect = new Command('property("' . $name . '", ' . $value . ')');

            $return->add($sideEffect);
        }

        return $return;
    }
}
?>
