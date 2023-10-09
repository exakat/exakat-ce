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

class SetClassPropertyDefinitionWithTypehint extends Complete {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                    );
    }

    public function analyze(): void {
        // $object->property->method()
        $this->atomIs('Propertydefinition', self::WITHOUT_CONSTANTS)
              ->as('property')
              ->outIs('DEFINITION')
              ->inIs('OBJECT')
              ->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->as('call')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->back('first')
              ->inIs('PPP')
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->hasNoLinkYet('DEFINITION', 'call')
              ->addETo('DEFINITION', 'call');
        $this->prepareQuery();

        // $object->property->property2
        $this->atomIs('Propertydefinition', self::WITHOUT_CONSTANTS)
              ->as('property')
              ->outIs('DEFINITION')
              ->inIs('OBJECT')
              ->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->as('call')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->inIs('PPP')
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->hasNoLinkYet('DEFINITION', 'call')
              ->addETo('DEFINITION', 'call');
        $this->prepareQuery();

        // $object->property::constant
        $this->atomIs('Propertydefinition', self::WITHOUT_CONSTANTS)
              ->as('property')
              ->outIs('DEFINITION')
              ->inIs('CLASS')
              ->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->as('call')
              ->outIs('CONSTANT')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->inIs('PPP')
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->hasNoLinkYet('DEFINITION', 'call')
              ->addETo('DEFINITION', 'call');
        $this->prepareQuery();
    }
}

?>
