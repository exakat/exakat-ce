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

class GoToMethod extends DSL {
    public function run(): Command {
        $this->assertArguments(1, func_num_args(), __METHOD__);
        list($name) = func_get_args();

        if (empty($name)) {
            return new Command(Query::STOP_QUERY);
        }

        if (is_array($name)) {
            $names = array_map('mb_strtolower', $name);
        } else {
            $names = array(mb_strtolower($name));
        }

        // also handle variables
        //assert($this->assertProperty($name));

        $gremlin = <<<'GREMLIN'
 out("METHOD", "MAGICMETHOD").hasLabel("Method", "Magicmethod")
.out("NAME").filter{ it.get().value("fullcode").toLowerCase() in ***}
.in("NAME")
GREMLIN;

        return new Command($gremlin, array($names));
    }
}
?>
