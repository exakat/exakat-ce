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

class SetClassRemoteDefinitionWithGlobal extends Complete {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                     'Complete/GlobalDefinitions',
                    );
    }

    public function analyze(): void {
        // $global->method()
        // global $global
        // $global = new Class()
        // class class { function method() {} }
        $this->atomIs(array('Methodcall', 'Staticmethodcall'), self::WITHOUT_CONSTANTS)
              ->as('method')
              ->hasNoIn('DEFINITION')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->inIs('METHOD')
              ->outIs(array('OBJECT', 'CLASS'))

              ->atomIs(array('Variableobject', 'Variable'))
              ->inIs('DEFINITION')
              ->inIs('DEFINITION')
              ->atomIs('Virtualglobal', self::WITHOUT_CONSTANTS)
              ->outIs('DEFINITION')
              ->atomIs('Variabledefinition', self::WITHOUT_CONSTANTS)
              ->outIs('DEFAULT')
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')

              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('METHOD')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->hasNoLinkYet('DEFINITION', 'method')
              ->addETo('DEFINITION', 'method');
        $this->prepareQuery();

        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->as('member')
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs(array('Name', 'Staticpropertyname'), self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->inIs('MEMBER')
              ->outIs('OBJECT')
              ->atomIs('Variableobject')
              ->inIs('DEFINITION')
              ->inIs('DEFINITION')
              ->atomIs('Virtualglobal', self::WITHOUT_CONSTANTS)
              ->outIs('DEFINITION')
              ->atomIs('Variabledefinition', self::WITHOUT_CONSTANTS)
              ->outIs('DEFAULT')
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')

              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
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
              ->atomIs(array('Name', 'Staticpropertyname'), self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->inIs('MEMBER')
              ->outIs('CLASS')
              ->atomIs('Variable')
              ->inIs('DEFINITION')
              ->inIs('DEFINITION')
              ->atomIs('Virtualglobal', self::WITHOUT_CONSTANTS)
              ->outIs('DEFINITION')
              ->atomIs('Variabledefinition', self::WITHOUT_CONSTANTS)
              ->outIs('DEFAULT')
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')

              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->hasNoLinkYet('DEFINITION', 'member')
              ->addETo('DEFINITION', 'member');
        $this->prepareQuery();

        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->as('member')
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->inIs('CONSTANT')
              ->outIs('CLASS')
              ->atomIs('Variable')
              ->inIs('DEFINITION')
              ->inIs('DEFINITION')
              ->atomIs('Virtualglobal', self::WITHOUT_CONSTANTS)
              ->outIs('DEFINITION')
              ->atomIs('Variabledefinition', self::WITHOUT_CONSTANTS)
              ->outIs('DEFAULT')
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_SENSITIVE)
              ->hasNoLinkYet('DEFINITION', 'member')
              ->addETo('DEFINITION', 'member');
        $this->prepareQuery();
    }
}

?>
