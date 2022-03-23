<?php declare(strict_types = 1);
/*
 * Copyright 2012-2021 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

namespace Exakat\Analyzer\Complete;

use Exakat\Analyzer\Analyzer;

class VariableTypehint extends Analyzer {
    public function analyze(): void {
        // extends to global variables

        // adding integer typehint
        $this->atomIs('Variabledefinition')
             ->hasNoOut('TYPEHINT')
             // only one default
             ->filter(
                $this->side()
                     ->outIs('DEFAULT')
                     ->raw('count().is(eq(1))')
             )

             ->outIs('DEFAULT')
             ->atomIs(array('Integer', 'Null', 'String', 'Magicconstant', 'Heredoc', 'Arrayliteral', 'Boolean', 'Float'), self::WITH_CONSTANTS)
             ->savePropertyAs('label', 'atomValue')
             ->back('first')
             ->initVariable('fnp', "''")
             ->raw(<<<'GREMLIN'
        sideEffect{ 
            fnp = "DEFAULT VALUE";
            switch(atomValue) {
                case 'Integer'       : fnp = "\\int";        break;
                case 'Null'          : fnp = "\\null";       break;
                case 'String'        : fnp = "\\string";     break;
                case 'Heredoc'       : fnp = "\\string";     break;
                case 'Magicconstant' : fnp = "\\string";     break;
                case 'Arrayliteral'  : fnp = "\\array";      break;
                case 'Boolean'       : fnp = "\\bool";       break;
                default : 
                    fnp = "DEFAULT TYPE";break;
            }
        }
GREMLIN
)
             ->addAtom('Scalartypehint', array(
                'fullcode'   => 'fnp',
                'fullnspath' => 'fnp',
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
             ))

             ->addEFrom('TYPEHINT', 'first')
             ->back('first');
        $this->prepareQuery();

        // adding returned type
        $this->atomIs('Variabledefinition')
             ->hasNoOut('TYPEHINT')
             // only one default
             ->filter(
                $this->side()
                     ->outIs('DEFAULT')
                     ->raw('count().is(eq(1))')
             )

             ->outIs('DEFAULT')
             ->atomIs(array('Functioncall', 'Methodcall', 'Staticmethodcall'), self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->outIs('RETURNTYPE')
             ->duplicateNode()
             ->addEFrom('TYPEHINT', 'first')
             ->back('first');
        $this->prepareQuery();

        // adding new x() with class definition
        $this->atomIs('Variabledefinition')
             ->hasNoOut('TYPEHINT')
             // only one default
             // could be upgraded to multiple identical new
             ->filter(
                $this->side()
                     ->outIs('DEFAULT')
                     ->raw('count().is(eq(1))')
             )

             ->outIs('DEFAULT')
             ->atomIs(array('New'), self::WITH_CONSTANTS)
             ->outIs('NEW')
             ->inIs('DEFINITION')
             ->as('theClass')
             ->has('fullnspath')
             ->savePropertyAs('whole', 'definition')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
             ))
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));

    if (definition.property("isPhp").any())  { it.get().property("isPhp", true); }
    if (definition.property("isExt").any())  { it.get().property("isExt", true); }
    if (definition.property("isStub").any()) { it.get().property("isStub", true); }
}
GREMLIN
)
             ->as('typehint')
             ->addEFrom('TYPEHINT', 'first')
             ->back('typehint')
             ->addEFrom('DEFINITION', 'theClass')
             ->back('first');
        $this->prepareQuery();

        // adding new stdclass()
        $this->atomIs('Variabledefinition')
             ->hasNoOut('TYPEHINT')
             // only one default
             // could be upgraded to multiple identical new
             ->filter(
                $this->side()
                     ->outIs('DEFAULT')
                     ->raw('count().is(eq(1))')
             )

             ->outIs('DEFAULT')
             ->atomIs(array('New'), self::WITH_CONSTANTS)
             ->outIs('NEW')
             ->hasNoIn('DEFINITION')
             ->has('fullnspath')
             ->savePropertyAs('whole', 'definition')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
             ))
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));

    if (definition.property("isPhp").any()) { it.get().property("isPhp", true); }
    if (definition.property("isExt").any()) { it.get().property("isExt", true); }
    if (definition.property("isStub").any()) { it.get().property("isStub", true); }
}
GREMLIN
)
             ->addEFrom('TYPEHINT', 'first')
             ->back('first');
        $this->prepareQuery();

        // adding new stdclass()
        $this->atomIs('Variabledefinition')
             ->hasNoOut('TYPEHINT')
             // only one default
             // could be upgraded to multiple identical new
             ->filter(
                $this->side()
                     ->outIs('DEFAULT')
                     ->raw('count().is(eq(1))')
             )

             ->outIs('DEFAULT')
             ->atomIs(self::FUNCTIONS_CALLS, self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             // Method, functions, etc.
             ->outIs('RETURNTYPE')
             ->inIs('DEFINITION')
             ->has('fullnspath')
             ->savePropertyAs('whole', 'definition')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
             ))
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));

    if (definition.property("isPhp").any()) { it.get().property("isPhp", true); }
    if (definition.property("isExt").any()) { it.get().property("isExt", true); }
    if (definition.property("isStub").any()) { it.get().property("isStub", true); }
}
GREMLIN
)
             ->addEFrom('TYPEHINT', 'first')
             ->back('first');
        $this->prepareQuery();

    }
}

?>
