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

namespace Exakat\Analyzer\Complete;

class FollowClosureDefinition extends Complete {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                    );
    }

    public function analyze(): void {
        // immediate usage : in parenthesis (function () {})()
        $this->atomIs(array('Closure', 'Arrowfunction'), self::WITHOUT_CONSTANTS)
             ->inIsIE('RIGHT') // Skip all $closure =
             ->inIs('CODE')
             ->atomIs('Parenthesis')
             ->inIs('NAME')
             ->atomIs('Functioncall')
             ->hasNoIn('DEFINITION')
             ->addEFrom('DEFINITION', 'first');
        $this->prepareQuery();

        // local usage
        $this->atomIs(array('Closure', 'Arrowfunction'), self::WITHOUT_CONSTANTS)
             ->inIs('RIGHT')
             ->outIs('LEFT')
             ->inIs('DEFINITION')  // Find all variable usage
             ->outIs('DEFINITION')
             ->inIs('NAME')
             ->atomIs('Functioncall', self::WITHOUT_CONSTANTS)
             ->hasNoIn('DEFINITION')
             ->addEFrom('DEFINITION', 'first');
        $this->prepareQuery();

        // relayed usage foo(function(){}); function foo($a) { $a();}
        // relayed usage $d = function(){}; foo($d); function foo($a) { $a();}
        $this->atomIs('Functioncall')
             ->outIs('NAME')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->inIs('NAME')
             ->atomIs('Parameter')
             ->goToParameterUsage()
             ->atomIs(array('Closure', 'Arrowfunction'), self::WITH_VARIABLES)
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // First class Callable
        // immediate usage
        $this->atomIs('Callable', self::WITHOUT_CONSTANTS)
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))

             ->inIs('DEFINITION')
             ->as('definition')
             ->back('first')

             ->inIs('NAME')
             ->atomIs('Functioncall', self::WITHOUT_CONSTANTS)
             ->hasNoIn('DEFINITION')
             ->addEFrom('DEFINITION', 'definition');
        $this->prepareQuery();

        // local usage
        $this->atomIs('Callable', self::WITHOUT_CONSTANTS)
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))

             ->inIs('DEFINITION')
             ->as('definition')
             ->back('first')

             ->inIs('RIGHT')
             ->outIs('LEFT')
             ->inIs('DEFINITION')  // Find all variable usage
             ->outIs('DEFINITION')
             ->inIs('NAME')
             ->atomIs('Functioncall', self::WITHOUT_CONSTANTS)
             ->hasNoIn('DEFINITION')
             ->addEFrom('DEFINITION', 'definition');
        $this->prepareQuery();

        // On a method $o->b()
        $this->atomIs('Callable', self::WITHOUT_CONSTANTS)
             ->tokenIs('T_OBJECT_OPERATOR') // T_NULLSAFE_OBJECT_OPERATOR is not possible

             ->outIs('METHOD')
             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'method')
             ->back('first')

             ->outIs('OBJECT')
             ->inIs('DEFINITION')
             ->outIs('DEFAULT')
             ->atomIs(array('New'))
             ->outIs('NEW')
             ->inIs('DEFINITION')
             ->atomIs('Class')
             ->outIs('METHOD')
             ->as('definition')
             ->outIs('NAME')
             ->samePropertyAs('fullcode', 'method')
             ->back('first')

             ->inIs('RIGHT')
             ->outIs('LEFT')
             ->inIs('DEFINITION')  // Find all variable usage
             ->outIs('DEFINITION')
             ->inIs('NAME')
             ->atomIs('Functioncall', self::WITHOUT_CONSTANTS)
             ->hasNoIn('DEFINITION')
             ->addEFrom('DEFINITION', 'definition');
        $this->prepareQuery();

        // On a static method A::b()
        $this->atomIs('Callable', self::WITHOUT_CONSTANTS)
             ->tokenIs('T_DOUBLE_COLON')

             ->outIs('METHOD')
             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'method')
             ->back('first')

             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->atomIs('Class')
             ->outIs('METHOD')
             ->as('definition')
             ->outIs('NAME')
             ->samePropertyAs('fullcode', 'method')
             ->back('first')

             ->inIs('RIGHT')
             ->outIs('LEFT')
             ->inIs('DEFINITION')  // Find all variable usage
             ->outIs('DEFINITION')
             ->inIs('NAME')
             ->atomIs('Functioncall', self::WITHOUT_CONSTANTS)
             ->hasNoIn('DEFINITION')
             ->addEFrom('DEFINITION', 'definition');
        $this->prepareQuery();

        // more complex structures are missing ;
        // argument, properties, etc.
    }
}

?>
