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

use Exakat\Analyzer\Analyzer;

class GoToAllParentsTraits extends DSL {
    public function run(): Command {
        if (func_num_args() === 1) {
            list($self) = func_get_args();
        } else {
            $self = Analyzer::EXCLUDE_SELF;
        }

        /*
                    $command = new Command(<<<'GREMLIN'
        hasLabel('Class', 'Classanonymous', 'Enum', 'Trait')
        .repeat(
            __.union( __.out("USE").out("USE").in("DEFINITION"), 
                         __.out("EXTENDS").in("DEFINITION")
                    ).hasLabel('Class', 'Trait')
        ).emit().hasLabel('Class', 'Trait')
        GREMLIN
        );
                } else {
                    $command = new Command(<<<'GREMLIN'
        hasLabel('Class', 'Classanonymous', 'Enum', 'Trait')
        .union( __.identity(),
               __.repeat(
                    __.union( __.out("USE").out("USE").in("DEFINITION"), 
                                 __.out("EXTENDS").in("DEFINITION")
                            ).hasLabel('Class', 'Trait')
                            ).emit().hasLabel('Class', 'Trait')
        )

        GREMLIN
        );
        */

        if ($self === Analyzer::EXCLUDE_SELF) {
            $command = new Command(<<<'GREMLIN'
union( __.out("USE").out("USE").in("DEFINITION"), 
       __.out("EXTENDS").in("DEFINITION")
)
GREMLIN
            );
        } else {
            $command = new Command(<<<'GREMLIN'
union( __.identity(),
       __.out("USE").out("USE").in("DEFINITION"), 
       __.out("EXTENDS").in("DEFINITION")
)

GREMLIN
            );
        }

        return $command;
    }
}
?>
