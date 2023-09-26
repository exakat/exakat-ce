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

class SetParentDefinition extends Complete {
    public function analyze(): void {
        //parent:: -> class -> extends
        $this->atomIs(array('Newcall', 'Parent'), self::WITHOUT_CONSTANTS)
             ->fullnspathIs('\\parent', self::CASE_SENSITIVE)
             ->hasNoIn('DEFINITION')
             ->goToClass()
             ->outIs('EXTENDS')
             ->inIs('DEFINITION')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        $this->atomIs('String', self::WITHOUT_CONSTANTS)
             ->fullnspathIs('\\\\parent', self::CASE_SENSITIVE)
             ->hasNoIn('DEFINITION')
             ->as('parent')
             ->goToClass()
             ->outIs('EXTENDS')
             ->inIs('DEFINITION')
             ->as('origin')
             ->hasNoLinkYet('DEFINITION', 'parent')
             ->addETo('DEFINITION', 'parent');
        $this->prepareQuery();

        // new parent() to __construct
        $this->atomIs('New', self::WITHOUT_CONSTANTS)
        	 ->outIs('NEW')
             ->fullnspathIs('\\parent', self::CASE_SENSITIVE)
             ->as('origin')
             ->inIs('DEFINITION')
             ->atomIs('Class') // @todo more than just class?
             ->outIs('MAGICMETHOD')
             ->outIs('NAME')
             ->fullcodeIs('__construct', self::CASE_INSENSITIVE)
             ->inIs('NAME')
//             ->hasNoLinkYet('DEFINITION', 'first') 
// There is already one DEFINITION for the class
             ->addETo('DEFINITION', 'origin');
        $this->prepareQuery();

        // parent::$property : link to definition
        $this->atomIs('Parent', self::WITHOUT_CONSTANTS)
             ->inIs('CLASS')
             ->atomIs('Staticproperty')
             ->as('property')
             ->outIs('MEMBER')
             ->savePropertyAs('code', 'name')
             ->back('first')

             ->inIs('DEFINITION')
             ->as('parent')
             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('PPP')
             ->outIs('PPP')
             ->atomIs('Propertydefinition')
             ->as('definition')

             ->outIs('NAME')
             ->samePropertyAs('code', 'name')
             ->back('definition')

             ->hasNoLinkYet('DEFINITION', 'property')
             ->addETo('DEFINITION', 'property');
        $this->prepareQuery();

        // parent::constant : link to definition
        $this->atomIs('Parent', self::WITHOUT_CONSTANTS)
             ->inIs('CLASS')
             ->atomIs('Staticconstant')
             ->hasNoIn('DEFINITION')
             ->as('constant')
             ->outIs('CONSTANT')
             ->savePropertyAs('code', 'name')
             ->back('first')

             ->inIs('DEFINITION')
             ->as('parent')
             ->goToAllParents(self::INCLUDE_SELF)
             ->outIs('CONST')
             ->outIs('CONST')
             ->as('definition')

             ->outIs('NAME')
             ->samePropertyAs('code', 'name')
             ->back('definition')

             ->hasNoLinkYet('DEFINITION', 'constant')
             ->addETo('DEFINITION', 'constant');
        $this->prepareQuery();

        // parent::method() : link to definition
        $this->atomIs('Parent', self::WITHOUT_CONSTANTS)
             ->inIs('CLASS')
             ->atomIs('Staticmethodcall')
             ->as('method')
             ->outIs('METHOD')
             ->outIs('NAME')
             ->savePropertyAs('lccode', 'name')
             ->back('first')

             ->inIs('DEFINITION')
             ->as('parent')
             ->goToAllParents(self::INCLUDE_SELF)
             ->savePropertyAs('fullnspath', 'theParent')
             ->outIs('METHOD')
             ->as('definition')

             ->outIs('NAME')
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->back('definition')

             ->hasNoLinkYet('DEFINITION', 'method')
             ->addETo('DEFINITION', 'method');
        $this->prepareQuery();

        // adds property isStub
        $this->initStubs();

        $list = array('stubs'      => 'isStub',
                      //'phpCore'    => 'isPhp',
                      //'extensions' => 'isExt',
                     );

        foreach ($list as $type => $property) {
            // parent::method()
            $stubs = $this->$type->getClassStaticMethodList();
            $this->atomIs('Parent', self::WITHOUT_CONSTANTS)
                 ->inIs('CLASS')
                 ->as('methodcall')
                 ->atomIs('Staticmethodcall')
                 ->outIs('METHOD')
                 ->outIs('NAME')
                 ->savePropertyAs('fullcode', 'name')
                 ->back('first')

                 ->goToClass()
                 ->goToAllParents(self::INCLUDE_SELF) // go to last parent
                 ->outIs('EXTENDS')
                 ->savePropertyAs('fullnspath', 'theParent')

                 ->back('methodcall')
                 ->makeMethodFnp('methodFnp', 'theParent', 'name')
                 ->sameVariableAs('methodFnp', $stubs)
                 ->setProperty('fullnspath', 'methodFnp')
                 ->property($property, true)
                 ->back('first')
                 ->setProperty('fullnspath', 'theParent');
            $this->prepareQuery();

            // parent::constant
            $stubs = $this->$type->getClassConstantList();
            $this->atomIs('Parent', self::WITHOUT_CONSTANTS)
                 ->inIs('CLASS')
                 ->as('constant')
                 ->atomIs('Staticconstant')
                 ->outIs('CONSTANT')
                 ->savePropertyAs('fullcode', 'name')
                 ->back('first')

                 ->goToClass()
                 ->goToAllParents(self::INCLUDE_SELF) // go to last parent
                 ->outIs('EXTENDS')
                 ->savePropertyAs('fullnspath', 'theParent')

                 ->back('constant')
                 ->makeConstantFnp('constantFnp', 'theParent', 'name')
                 ->sameVariableAs('constantFnp', $stubs)
                 ->setProperty('fullnspath', 'constantFnp')
                 ->property($property, true)
                 ->back('first')
                 ->setProperty('fullnspath', 'theParent');
            $this->prepareQuery();

            // parent::$property
            $stubs = $this->$type->getClassStaticPropertyList();
            $this->atomIs('Parent', self::WITHOUT_CONSTANTS)
                 ->inIs('CLASS')
                 ->as('constant')
                 ->atomIs('Staticproperty')
                 ->outIs('MEMBER')
                 ->savePropertyAs('fullcode', 'name')
                 ->back('first')

                 ->goToClass()
                 ->goToAllParents(self::INCLUDE_SELF) // go to last parent
                 ->outIs('EXTENDS')
                 ->savePropertyAs('fullnspath', 'theParent')

                 ->back('constant')
                 ->makeConstantFnp('propertyFnp', 'theParent', 'name')
                 ->sameVariableAs('propertyFnp', $stubs)
                 ->setProperty('fullnspath', 'propertyFnp')
                 ->property($property, true)
                 ->back('first')
                 ->setProperty('fullnspath', 'theParent');
            $this->prepareQuery();
        }
    }
}

?>
