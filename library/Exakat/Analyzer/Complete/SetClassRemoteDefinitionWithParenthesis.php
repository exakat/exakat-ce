<?php declare(strict_types = 1);
/*
 * Copyright 2012-2024 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

class SetClassRemoteDefinitionWithParenthesis extends Complete {
    public function analyze(): void {
        // (new x)->foo()
        // (new x)::foo()
        $this->atomIs(array('Methodcall', 'Staticmethodcall'), self::WITHOUT_CONSTANTS)
              ->as('method')
              ->hasNoIn('DEFINITION')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->back('first')

              ->outIs(array('OBJECT', 'CLASS'))
              ->atomIs('Parenthesis', self::WITHOUT_CONSTANTS)
              ->outIs('CODE')
              ->optional(
                  $this->side()
                       ->atomIs('Assignation')
                       ->outIs('RIGHT')
              )
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->filter(
                  $this->side()
                     ->outIs('NAME')
                     ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              )
              ->hasNoLinkYet('DEFINITION', 'method')
              ->addETo('DEFINITION', 'method');
        $this->prepareQuery();

        // (new x)::foo()
        // (new x)::$p
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->as('member')
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')

              ->outIs(array('OBJECT', 'CLASS'))
              ->atomIs('Parenthesis', self::WITHOUT_CONSTANTS)
              ->outIs('CODE')
              ->optional(
                  $this->side()
                       ->atomIs('Assignation')
                       ->outIs('RIGHT')
              )
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->hasNoLinkYet('DEFINITION', 'member')
              ->addETo('DEFINITION', 'member');
        $this->prepareQuery();

        $this->atomIs('Staticproperty', self::WITHOUT_CONSTANTS)
              ->as('member')
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs('Staticpropertyname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')

              ->outIs('CLASS')
              ->atomIs('Parenthesis', self::WITHOUT_CONSTANTS)
              ->outIs('CODE')
              ->optional(
                  $this->side()
                       ->atomIs('Assignation')
                       ->outIs('RIGHT')
              )
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->hasNoLinkYet('DEFINITION', 'member')
              ->addETo('DEFINITION', 'member');
        $this->prepareQuery();

        // (new x)::FOO
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->as('constant')
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')

              ->outIs('CLASS')
              ->atomIs('Parenthesis', self::WITHOUT_CONSTANTS)
              ->outIs('CODE')
              ->optional(
                  $this->side()
                       ->atomIs('Assignation')
                       ->outIs('RIGHT')
              )
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->outIs('CONST')
              ->filter(
                  $this->side()
                     ->outIs('NAME')
                     ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              )
              ->hasNoLinkYet('DEFINITION', 'constant')
              ->addETo('DEFINITION', 'constant');
        $this->prepareQuery();
    }
}

?>
