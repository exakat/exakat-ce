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


class IsAnyOf extends DSL {
    public function run(): Command {
        assert(func_num_args() === 2, 'Wrong number of argument for ' . __METHOD__ . '. 2 are expected, ' . func_num_args() . ' provided');
        list($properties, $value) = func_get_args();

        assert(is_array($properties), 'Property argument should be an array. ' . gettype($properties) . " was provided\n");
        foreach ($properties as $property) {
            $this->assertProperty($property);
        }

        $command = array();
        if ($value === null) {
            foreach ($properties as $property) {
                $command[] = 'has("' . $property . '", null)';
            }
            /*        } elseif (in_array($property, self::BOOLEAN_PROPERTY, \STRICT_COMPARISON)) {
                        $value = $value === true ? 'true' : 'false';

                        return new Command('filter{ if ( it.get().properties("' . $property . '").any()) { ' . $value . ' == it.get().value("' . $property . '")} else {' . $value . ' == false; }; }');
                    */
        } elseif ($value === true) {
            foreach ($properties as $property) {
                $command[] = 'has("' . $property . '", true)';
            }
        } elseif ($value === false) {
            foreach ($properties as $property) {
                $command[] = 'has("' . $property . '", false)';
            }
        } elseif (is_int($value)) {
            foreach ($properties as $property) {
                $command[] = 'has("' . $property . '", ' . $value . ')';
            }
        } elseif (is_string($value)) {
            assert(false, 'Not done yet');
            return new Command('has("' . $property . '", ***)', array($value));
        } elseif (is_array($value)) {
            assert(false, 'Not done yet');
            return new Command('has("' . $property . '", within(***))', array($value));
        } else {
            assert(false, 'Not understood type for is : ' . gettype($value));
        }

        return new Command('or(' . join(', ', $command) . ')');
    }
}
?>
