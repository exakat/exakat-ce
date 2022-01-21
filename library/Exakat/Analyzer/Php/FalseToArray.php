<?php
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

namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;

class FalseToArray extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                    );
    }

    public function analyze() : void {
        // $a = false; $a[3]
        $this->atomIs(array('Array', 'Arrayappend'))
             ->outIs(array('VARIABLE', 'APPEND'))
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('DEFAULT')
             ->atomIs('Boolean')
             ->back('first');
        $this->prepareQuery();

        // private bool $a; $a[3]
        $this->atomIs(array('Array', 'Arrayappend'))
             ->outIs(array('VARIABLE', 'APPEND'))
             ->goToTypehint()
             ->atomIs('Scalartypehint')
             ->fullnspathIs('\bool')
             ->back('first');
        $this->prepareQuery();

        // function foo() : false {} $a = foo(); $a[3]
        $this->atomIs('Array')
             ->outIs('VARIABLE')
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('DEFAULT')
             ->atomIs(self::FUNCTIONS_CALLS)
             ->inis('DEFINITION')
             ->outIs('RETURNTYPE')
             ->atomIs('Scalartypehint')
             ->fullnspathIs(array('\false', '\bool'))
             ->back('first');
        $this->prepareQuery();

        // function foo() : false {} $a = foo(); $a[3]
        $this->atomIs('Array')
             ->outIs('VARIABLE')
             ->atomIs(self::FUNCTIONS_CALLS)
             ->inis('DEFINITION')
             ->outIs('RETURNTYPE')
             ->atomIs('Scalartypehint')
             ->fullnspathIs(array('\false', '\bool'))
             ->back('first');
        $this->prepareQuery();

        $functions = array_merge($this->methods->getFunctionsByReturnType('bool'),
                                 $this->methods->getFunctionsByReturnType('false'),
                                );
        // function foo() : false {} $a = foo(); $a[3]
        $this->atomIs('Array')
             ->outIs('VARIABLE')
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('DEFAULT')
             ->atomIs('Functioncall')
             ->fullnspathIs($functions)
             ->back('first');
        $this->prepareQuery();

    }
}

?>
