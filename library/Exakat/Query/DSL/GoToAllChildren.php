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

use Exakat\Analyzer\Analyzer;

class GoToAllChildren extends DSL {
    public function run(): Command {
        switch(func_num_args()) {
            case 1:
                list($self) = func_get_args();
                if (!in_array($self, array(Analyzer::INCLUDE_SELF, Analyzer::EXCLUDE_SELF), STRICT_COMPARISON)) {
                    $self = Analyzer::INCLUDE_SELF;
                }
                break;

            default:
                $self = Analyzer::INCLUDE_SELF;
        }

        $MAX_LOOPING = self::$MAX_LOOPING;

        if ($self === Analyzer::EXCLUDE_SELF) {
            $command = new Command(<<<GREMLIN
 as("gotoallchildren")
.repeat( __.out("DEFINITION")
           .in("EXTENDS", "IMPLEMENTS")
           .simplePath().from("gotoallchildren")
          )
          .emit( )
          .times($MAX_LOOPING)
GREMLIN
);
            return $command;
        } else {
            $command = new Command(<<<GREMLIN
 as("gotoallchildren")
.emit( )
.repeat( __.out("DEFINITION")
           .in("EXTENDS", "IMPLEMENTS")
           .simplePath().from("gotoallchildren")
          )
          .times($MAX_LOOPING)
GREMLIN
);
            return $command;
        }
    }
}
?>
