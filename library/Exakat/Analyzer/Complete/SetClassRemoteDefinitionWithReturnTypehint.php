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

class SetClassRemoteDefinitionWithReturnTypehint extends Complete {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                    );
    }

    public function analyze(): void {
        // class a { function b() }; function foo() : a {}; $a = foo(); $a->b()
        // class a { function b() }; function foo() : a {}; foo()->b()
        $this->atomIs(self::FUNCTIONS_ALL, self::WITHOUT_CONSTANTS)
              ->hasOut('RETURNTYPE')
              ->outIs('DEFINITION')
              ->atomIs(self::FUNCTIONS_CALLS, self::WITHOUT_CONSTANTS)
              ->optional(
                  $this->side()
                       ->inIs('DEFAULT')
                       ->atomIs(array('Propertydefinition', 'Variabledefinition', 'Globaldefinition', 'Staticdefinition'), self::WITHOUT_CONSTANTS)
                       ->outIs('DEFINITION')
              )
              ->inIs(array('OBJECT', 'CLASS'))
              ->outIs('METHOD')
              ->atomIs(array('Methodcallname', 'Staticmethodcall'), self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->inIs('METHOD')
              ->hasNoIn('DEFINITION')
              ->as('method')
              ->back('first')

              ->outIs('RETURNTYPE')
              ->inIs('DEFINITION')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('METHOD')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->hasNoLinkYet('DEFINITION', 'method')
              ->addETo('DEFINITION', 'method');
        $this->prepareQuery();

        // class a { private $b }; function foo() : a {}; $a = foo(); $a->b
        // class a { private $b }; function foo() : a {}; foo()->b
        $this->atomIs(self::FUNCTIONS_ALL, self::WITHOUT_CONSTANTS)
             ->hasOut('RETURNTYPE')
             ->outIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_CALLS, self::WITHOUT_CONSTANTS)
              ->optional(
                  $this->side()
                       ->inIs('DEFAULT')
                       ->atomIs(array('Propertydefinition', 'Variabledefinition', 'Globaldefinition', 'Staticdefinition'), self::WITHOUT_CONSTANTS)
                       ->outIs('DEFINITION')
              )

             ->inIs('OBJECT')
             ->hasNoIn('DEFINITION')
             ->as('member')
             ->atomIs('Member', self::WITHOUT_CONSTANTS)
             ->outIs('MEMBER')
             ->savePropertyAs('code', 'name')
             ->back('first')

             ->outIs('RETURNTYPE')
             ->inIs('DEFINITION')
             ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
             ->goToAllParents(self::INCLUDE_SELF)
             ->outIs('PPP')
             ->outIs('PPP')
             ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
             ->hasNoLinkYet('DEFINITION', 'member')
             ->addETo('DEFINITION', 'member');
        $this->prepareQuery();


        // $b = foo(); $b->p; function foo() : C {}
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->as('member')
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->inIs('MEMBER')
              ->outIs('OBJECT')
              ->inIs('DEFINITION')
              ->atomIs(array('Variabledefinition', 'Parametername', 'Propertydefinition', 'Globaldefinition', 'Staticdefinition', 'Virtualproperty'), self::WITHOUT_CONSTANTS)
              ->outIs('DEFAULT')
              ->atomIs(self::FUNCTIONS_CALLS, self::WITHOUT_CONSTANTS)
              ->inIs('DEFINITION')
              ->outIs('RETURNTYPE')
              ->inIs('DEFINITION')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->hasNoLinkYet('DEFINITION', 'member')
              ->addETo('DEFINITION', 'member');
        $this->prepareQuery();

        // $b = foo(); $b::p; function foo() : C {}
        $this->atomIs('Staticproperty', self::WITHOUT_CONSTANTS)
              ->as('member')
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->savePropertyAs('code', 'name')
              ->inIs('MEMBER')
              ->outIs('CLASS')
              ->optional(
                  $this->side()
                       ->inIs('DEFINITION')
                       ->atomIs(array('Propertydefinition', 'Variabledefinition', 'Globaldefinition', 'Staticdefinition'), self::WITHOUT_CONSTANTS)
                       ->outIs('DEFAULT')
              )
              ->atomIs(self::FUNCTIONS_CALLS, self::WITHOUT_CONSTANTS)
              ->inIs('DEFINITION')
              ->outIs('RETURNTYPE')
              ->inIs('DEFINITION')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->hasNoLinkYet('DEFINITION', 'member')
              ->addETo('DEFINITION', 'member');
        $this->prepareQuery();
    }
}

?>
