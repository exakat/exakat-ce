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


class CollectPhpCalls extends DSL {
    public function run(): Command {
        switch (func_num_args()) {
            case 0:
                $extraGremlin = '';
                break;

            case 1:
                list($maximum) = func_get_args();
                $maximum = abs((int) $maximum);
                $extraGremlin = ".is(gt($maximum))";
                break;

            default:
                assert(false, 'wrong number of argument for ' . __METHOD__);
        }

        $linksDown = self::$linksDown;
        $MAX_LOOPING = self::$MAX_LOOPING;

        $command = new Command(<<<GREMLIN
where(
    __.emit( ).repeat( __.out({$linksDown}).not(hasLabel("Closure", "Classanonymous", "Function", "Arrowfunction", "Class", "Trait", "Interface", "Enum")) ).times($MAX_LOOPING)
      .hasLabel('Functioncall')
      .or(
        __.has("isPhp"),
        __.has("isExt")
      )
      .count()$extraGremlin
)
GREMLIN
        );
        return $command;
    }
}
?>
