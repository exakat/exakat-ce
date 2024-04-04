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

class SetClassRemoteDefinitionWithLocalNew extends Complete {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                    );
    }

    public function analyze(): void {
        // $a = new X(); $a->m1();
        // $a = new $this; $a->m1();
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->back('first')

              ->outIs('OBJECT')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs(array('Variabledefinition', 'Parametername', 'Propertydefinition', 'Globaldefinition', 'Staticdefinition', 'Virtualproperty'), self::WITHOUT_CONSTANTS)
              ->outIs('DEFAULT')
              ->atomIs(array('New', 'Clone'), self::WITHOUT_CONSTANTS)
              ->outIs(array('NEW', 'CLONE'))
              ->inIs('DEFINITION')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs('METHOD')
              ->filter(
                  $this->side()
                     ->outIs('NAME')
                     ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              )
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // $a = new X(); $a->p;
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->as('member')
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')

              ->outIs('OBJECT')
              ->inIs('DEFINITION')
              ->atomIs(array('Variabledefinition', 'Parametername', 'Propertydefinition', 'Globaldefinition', 'Staticdefinition', 'Virtualproperty'), self::WITHOUT_CONSTANTS)
              ->outIs('DEFAULT')
              ->atomIs(array('New', 'Clone'), self::WITHOUT_CONSTANTS)
              ->outIs(array('NEW', 'CLONE'))
              ->inIs('DEFINITION')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->atomIs('Propertydefinition')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->hasNoLinkYet('DEFINITION', 'member')
              ->addETo('DEFINITION', 'member');
        $this->prepareQuery();

        // @todo (new x)->method()
    }
}

?>
