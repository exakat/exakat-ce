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

class GoToTypehint extends DSL {
    public function run(): Command {
        // todo limit to one type of SOURCE

        // constants, variables, static member, variableoboject, etc.

        $gremlin = <<<'GREMLIN'
coalesce(
    __.hasLabel("Variable", "Variableobject", "Variablearray").in("DEFINITION").in("NAME").hasLabel("Parameter").out("TYPEHINT"),
    __.hasLabel("Member").in("DEFINITION").hasLabel("Propertydefinition").in("PPP").hasLabel("Ppp").out("TYPEHINT"),
    __.hasLabel("Staticproperty").in("DEFINITION").hasLabel("Propertydefinition").in("PPP").hasLabel("Ppp").out("TYPEHINT"),
    __.hasLabel("Staticconstant").in("DEFINITION").hasLabel("Constant").out("TYPEHINT"),
    __.hasLabel("Identifier", "Nsname").in("DEFINITION").hasLabel("Constant").out("TYPEHINT")
)
GREMLIN;

        return new Command($gremlin);
    }
}
?>
