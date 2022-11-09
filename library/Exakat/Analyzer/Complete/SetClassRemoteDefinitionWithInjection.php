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

namespace Exakat\Analyzer\Complete;

class SetClassRemoteDefinitionWithInjection extends Complete {
    public function analyze(): void {
        // function foo(J $j) { $j->method()
        $this->atomIs('Parameter', self::WITHOUT_CONSTANTS)
              ->as('parameter')
              ->outIs('NAME')
              ->outIs('DEFINITION')
              ->inIs(array('OBJECT', 'CLASS'))
              ->atomIs(array('Methodcall', 'Staticmethodcall'), self::WITHOUT_CONSTANTS)
              ->as('call')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->back('first')
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->addETo('DEFINITION', 'call');
        $this->prepareQuery();

        // function foo(J $j) { $j->p
        $this->atomIs('Parameter', self::WITHOUT_CONSTANTS)
              ->as('parameter')
              ->outIs('NAME')
              ->outIs('DEFINITION')
              ->inIs('OBJECT')
              ->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->as('call')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->addETo('DEFINITION', 'call');
        $this->prepareQuery();

        // function foo(J $j) { $j::$p
        $this->atomIs('Parameter', self::WITHOUT_CONSTANTS)
              ->as('parameter')
              ->outIs('NAME')
              ->outIs('DEFINITION')
              ->inIs('CLASS')
              ->atomIs('Staticproperty', self::WITHOUT_CONSTANTS)
              ->as('call')
              ->outIs('MEMBER')
              ->atomIs('Staticpropertyname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->addETo('DEFINITION', 'call');
        $this->prepareQuery();

        // function foo(J $j) { $j::X
        $this->atomIs('Parameter', self::WITHOUT_CONSTANTS)
              ->as('parameter')
              ->outIs('NAME')
              ->outIs('DEFINITION')
              ->inIs('CLASS')
              ->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->as('call')
              ->outIs('CONSTANT')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->addETo('DEFINITION', 'call');
        $this->prepareQuery();
    }
}

?>