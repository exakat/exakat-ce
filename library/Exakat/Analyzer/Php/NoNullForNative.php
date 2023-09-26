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

namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;
use Exakat\Data\Methods;

class NoNullForNative extends Analyzer {
    public function dependsOn(): array {
        return array('Variables/IsLocalConstant',
                     'Complete/CreateDefaultValues',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                     'Complete/SetClassRemoteDefinitionWithGlobal',
                     'Complete/SetClassRemoteDefinitionWithInjection',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                     'Complete/SetClassRemoteDefinitionWithParenthesis',
                     'Complete/SetClassRemoteDefinitionWithReturnTypehint',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                    );
    }

    public function analyze(): void {
        $returnNull = $this->readStubs('getFunctionsByReturnType', array('null', Methods::LOOSE));
        $acceptNull = exakat('methods')->getArgsByType('null', Methods::INVERSE);

        $called = array_flip($this->called->getCalledFunctions());
        $calledClasses = array_flip($this->called->getCalledClasses());

        $functions 	  = array();
        $allMethods   = array();

        foreach ($acceptNull as $position => $list) {
            foreach ($list as $function) {
                if (!str_contains($function, '::')  ) {
                    if (isset($called[$function])) {
                        $functions[$position][] = $function;
                    }
                } else {
                    list($class, $method) = explode('::', $function, 2);
                    if (isset($calledClasses[$class])) {
                        $allMethods[$class][$position][] = $method;
                    }
                }
            }
        }

        // echo substr('123', null, 1);
        foreach ($functions as $position => $fnp) {
            // NULL literal
            $this->atomIs('Functioncall')
                 ->analyzerIsNot('self')
                 ->is('isPhp', true)
                 ->fullnspathIs($fnp)
                 ->outWithRank('ARGUMENT', $position)
                 ->atomIs('Null', self::WITH_CONSTANTS)
                 ->back('first');
            $this->prepareQuery();

            // from php functions
            $this->atomIs('Functioncall')
                 ->analyzerIsNot('self')
                 ->is('isPhp', true)
                 ->fullnspathIs($fnp)
                 ->outWithRank('ARGUMENT', $position)
                 ->atomIs(self::FUNCTIONS_CALLS, self::WITH_VARIABLES)
                 ->is('isPhp', true)
                 ->fullnspathIs($returnNull)
                 ->back('first');
            $this->prepareQuery();

            // from custom variables
            $this->atomIs('Functioncall')
                 ->analyzerIsNot('self')
                 ->is('isPhp', true)
                 ->fullnspathIs($fnp)
                 ->outWithRank('ARGUMENT', $position)
                 ->atomIs(self::FUNCTIONS_CALLS, self::WITH_VARIABLES)
                 ->inIs('DEFINITION')
                 ->isNullable()
                 ->back('first');
            $this->prepareQuery();

            // from typed variables
            $this->atomIs('Functioncall')
                 ->analyzerIsNot('self')
                 ->is('isPhp', true)
                 ->fullnspathIs($fnp)
                 ->outWithRank('ARGUMENT', $position)
                 ->atomIs('Variable')
                 ->inIs('DEFINITION')
                 ->inIs('NAME')
                 // check is it tested for null
                 ->not(
                     $this->side()
                          ->outIs('NAME')
                          ->outIs('DEFINITION')
                          ->inIs(array('LEFT', 'RIGHT'))
                          ->atomIs('Comparison') // operator is not important
                          ->outIs(array('LEFT', 'RIGHT'))
                          ->atomIs('Null')
                 )
                 ->isNullable()
                 ->back('first');
            $this->prepareQuery();

            // from typed properties
            $this->atomIs('Functioncall')
                 ->analyzerIsNot('self')
                 ->is('isPhp', true)
                 ->fullnspathIs($fnp)
                 ->outWithRank('ARGUMENT', $position)
                 ->atomIs('Member')
                 ->inIs('DEFINITION')
                 ->inIs('PPP')
                 ->isNullable()
                 ->back('first');
            $this->prepareQuery();
        }

        foreach ($allMethods as $class => $methods) {
            foreach ($methods as $position => $name) {
                $this->atomIs('Methodcall')
                     ->analyzerIsNot('self')
                     ->outIs('OBJECT')
                     ->inIs('DEFINITION')
                     ->inIs('PPP')
                     ->outIs('TYPEHINT')
                     ->fullnspathIs($class)
                     ->back('first')

                     ->outIs('METHOD')
                     ->outIs('NAME')
                     ->fullcodeIs($name)

                     ->inIs('NAME')
                     ->outWithRank('ARGUMENT', $position)
                     ->atomIs('Null', self::WITH_CONSTANTS)
                     ->back('first');
                $this->prepareQuery();
            }
        }

        // custom function which may return null with type
    }
}

?>
