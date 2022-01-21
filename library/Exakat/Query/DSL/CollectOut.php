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


class CollectOut extends DSL {
    public function run(): Command {
        if (func_num_args() === 2) {
            list($variable, $out) = func_get_args();
        } else {
            assert(false, __METHOD__. " requires 2 arguments, ".func_num_args(). " passed.");
        }

        $this->assertVariable($variable, self::VARIABLE_WRITE);
        $this->assertLink($out);

        $MAX_LOOPING = self::$MAX_LOOPING;

        return new Command(<<<GREMLIN
where( 
    __.sideEffect{ $variable = []; }
      .out("$out")
      .sideEffect{ $variable.add(it.get().value("fullcode")) ; }
      .fold() 
) 

GREMLIN
);
    }
}
?>
