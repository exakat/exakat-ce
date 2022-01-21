<?php
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
    public function analyze() : void {
        // extends to global variables
        
        // adding integer typehint
        $this->atomIs('Variabledefinition')

             ->hasNoOut('TYPEHINT')
             // only one default 
             ->filter(
                $this->side()
                     ->raw('sideEffect{ s = [:];}.out("DEFAULT").sideEffect{ s[it.get().label()] = 1;}.fold().filter{ s.size() == 1; }')
             )

             ->outIs('DEFAULT')
             ->atomIs(array('Integer', 'Null', 'String', 'Arrayliteral', 'Boolean', 'Float'), self::WITH_CONSTANTS)
             ->savePropertyAs('label', 'atomValue')
             ->back('first')
             ->raw(<<<GREMLIN
        sideEffect{ 
            fnp = "DEFAULT VALUE";
            switch(atomValue) {
                case 'Integer'      : fnp = "\\\\int";        break;
                case 'Null'         : fnp = "\\\\null";       break;
                case 'String'       : fnp = "\\\\string";     break;
                case 'Arrayliteral' : fnp = "\\\\array";      break;
                case 'Boolean'      : fnp = "\\\\bool";       break;
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
             ->as("theClass")
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')
             ->addAtom('Scalartypehint', array(
                'fullcode'   => 'fnp',
                'fullnspath' => 'fnp',
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
             ))
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
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')
             ->addAtom('Identifier', array(
                'fullcode'   => 'fnp',
                'fullnspath' => 'fnp',
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
             ))
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
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')
             ->addAtom('Identifier', array(
                'fullcode'   => 'fnp',
                'fullnspath' => 'fnp',
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
             ))
             ->addEFrom('TYPEHINT', 'first')
             ->back('first');
        $this->prepareQuery();

    }
}

?>
