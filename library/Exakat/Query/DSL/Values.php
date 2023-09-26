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

class Values extends DSL {
    public function run(): Command {
        list($property) = func_get_args();

        assert($this->assertProperty($property));

        if (is_array($property)) {
            $as = array();
            $select = array();
            $by = array();
            foreach ($property as $id => $p) {
                $as[] = 'as("values' . $id . '")';
                $select[] = '"values' . $id . '"';
                $by[] = 'by("' . $p . '")';
            }
            $command = implode('.', $as) . '.local( __.select(' . implode(', ', $select) . ').' . implode('.', $by) . '.fold())';
        } elseif (is_string($property)) {
            $command = "values(\"$property\")";
        } else {
            assert(false, 'Wrong type for property : ' . gettype($property));
        }

        return new Command($command);
    }
}
?>
