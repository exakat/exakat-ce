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


class GoToFirstParent extends DSL {
    public function run(): Command {
        list($name) = func_get_args();

        // @todo : assert $name exists and is an atom

        $command = new Command(<<<GREMLIN
until( __.out("METHOD", "MAGICMETHOD").out("NAME").filter{ it.get().value("lccode") == $name.value("lccode")}).
 repeat( __.as("firstParent").outE().hasLabel("EXTENDS").not(has("extra")).inV().in("DEFINITION").hasLabel("Class").simplePath().from("firstParent")).emit()
 .out("METHOD", "MAGICMETHOD").out("NAME").filter{ it.get().value("lccode") == $name.value("lccode")}.in("NAME")
GREMLIN
        );
        return $command;
    }
}
?>
