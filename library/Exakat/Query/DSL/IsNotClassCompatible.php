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

class IsNotClassCompatible extends DSL {
    public function run(): Command {
        assert(func_num_args() === 2, 'Wrong number of argument for ' . __METHOD__ . '. 2 is expected, ' . func_num_args() . ' provided');
        list($fnp, $typehint) = func_get_args();

        // ONE or OR :all are missing
        // AND : one missing is enough

        $MAX_LOOPING = self::$MAX_LOOPING;
        $gremlin = <<<GREMLIN
 sideEffect{ t = $typehint;
             fnp = $fnp;
             if (fnp instanceof String) {
                fnp = [fnp];
             }
  }
    // collect all types available
.where( 
    __.sideEffect{ x = []; }
      .union(
                __.filter{ true },
                __.out("EXTENDS", "IMPLEMENTS").emit().repeat(__.in("DEFINITION").out("EXTENDS", "IMPLEMENTS")).times($MAX_LOOPING)
      )
      .has("fullnspath")
      .sideEffect{ x.add(it.get().value("fullnspath")) ; }
      .fold() 
)
.filter{
    if (t == "one") {
        x.intersect(fnp).size() == 0;
    } else if (t == "or") {
        x.intersect(fnp).size() == 0;
    } else if (t == "and") {
        fnp.findAll{ fnp ->
            fnp in x
        }.size() != fnp.size();
    } else {
        // We should never go here
        die; 
        false;
    }
}

GREMLIN;

        return new Command($gremlin);
    }
}
?>
