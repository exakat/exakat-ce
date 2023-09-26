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


class FollowAllCalls extends DSL {
    public function run(): Command {
        switch (func_num_args()) {
            case 1:
                $loopings = (int) func_get_arg(0);
                break;

            default:
                $loopings = self::$MAX_LOOPING;
                break;
        }

        return new Command(<<<GREMLIN
emit().repeat(
    __.out()
).times($loopings).hasLabel('Functioncall', 'Methodcall', 'Newcall', 'Staticmethodcall')
.in('DEFINITION').hasLabel('Function', 'Closure', 'Arrowfunction', 'Method', 'Magicmethod')
GREMLIN
        );
    }
}
?>