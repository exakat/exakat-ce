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


class CollectOutOne extends DSL {
    public function run(): Command {
        if (func_num_args() === 3) {
            list($variable, $out, $property) = func_get_args();
        } elseif (func_num_args() === 2) {
            list($variable, $out) = func_get_args();
            $property = 'fullcode';
        } else {
            assert(false, __METHOD__ . ' requires 2 or 3 arguments, ' . func_num_args() . ' passed.');
        }

        $this->assertVariable($variable, self::VARIABLE_WRITE);
        $this->assertLink($out);
        $this->assertProperty($property);

        if (is_array($out)) {
            $out = makeList($out);
        } else {
            $out = '"' . $out . '"';
        }

        return new Command(<<<GREMLIN
sideEffect( 
    __.sideEffect{ $variable = null; }
      .out($out)
      .has("$property")
      .sideEffect{ $variable = it.get().value("$property") ; }
) 

GREMLIN
        );
    }
}
?>
