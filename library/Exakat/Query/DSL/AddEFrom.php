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

use Exakat\Exceptions\WrongNumberOfArguments;

class AddEFrom extends DSL {
    public function run(): Command {
        if (func_num_args() === 2) {
            list($edgeName, $from) = func_get_args();
            $options = array();
        } elseif (func_num_args() === 3) {
            list($edgeName, $from, $options) = func_get_args();
            assert(is_array($options), 'Edge options must be an array');
        } else {
            throw new WrongNumberOfArguments(__METHOD__, func_num_args(), 3);
        }


        if (is_int($from)) {
            $from = ' __.V(' . $from . ') ';
        } else {
            assert($this->assertLabel($from, self::LABEL_GO));
            $from = '"' . $from . '"';
        }

        $properties = array();
        foreach ($options as $name => $value) {
            $properties[] = ".property(\"$name\", $value)";
        }
        $properties = implode('', $properties);

        return new Command("addE(\"$edgeName\").from($from)$properties");
    }
}
?>
