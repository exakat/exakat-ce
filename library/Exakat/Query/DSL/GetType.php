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


class GetType extends DSL {
    public function run(): Command {
        list($variable) = func_get_args();

        return new Command(<<<GREMLIN
coalesce(
    __.hasLabel("String", "Heredoc", "Concatenation").sideEffect{ type = "\\\\string"; },
    __.hasLabel("Boolean").sideEffect{ type = "\\\\bool"; },
    __.hasLabel("Integer").sideEffect{ type = "\\\\int"; },
    __.hasLabel("Float").sideEffect{ type = "\\\\float"; },
    __.hasLabel("Arrayliteral").sideEffect{ type = "\\\\array"; },

    __.hasLabel("New").out("NEW").has("fullnspath").sideEffect{ type = it.get().value("fullnspath");},
    __.hasLabel("Functioncall", "Methodcall", "Staticmethodcall").in("DEFINITION").has("typehint", "one").out("RETURNTYPE").has("fullnspath").sideEffect{ type = it.get().value("fullnspath");},

    __.hasLabel("Variable").in("DEFINITION").optional(__.in("NAME")).has("typehint","one").out("TYPEHINT").has('fullnspath').sideEffect{ type = it.get().values("fullnspath"); },
    __.hasLabel("Member", "Staticproperty").in("DEFINITION").hasLabel("Propertydefinition").in("PPP").has("typehint","one").out("TYPEHINT").has('fullnspath').sideEffect{ type = it.get().value("fullnspath"); }
).sideEffect{ $variable = type; }

GREMLIN
        );
    }
}
?>
