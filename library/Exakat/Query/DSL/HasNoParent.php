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

use Exakat\Query\Query;

class HasNoParent extends DSL {
    public function run(): Command {
        assert(func_num_args() === 2, 'Wrong number of argument for ' . __METHOD__ . '. 2 are expected, ' . func_num_args() . ' provided');
        list($parentClass, $ins) = func_get_args();

        if (empty($ins)) {
            return new Command(Query::NO_QUERY);
        }

        $this->assertAtom($parentClass);
        $this->assertLink($ins);
        $diff = $this->normalizeAtoms($parentClass);

        if (empty($diff)) {
            return new Command(Query::NO_QUERY);
        }

        $in = $this->makeLinks($ins, 'in');

        return new Command("not( where( __$in.hasLabel(within(***)) ) )", array($diff));
    }
}
?>
