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

class CreateMagicProperty extends Complete {
    public function dependsOn(): array {
        return array('Complete/OverwrittenProperties',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                     'Complete/CreateDefaultValues',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                     'Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // Missing : typehinted properties, return typehint, clone

        // link to __get
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->atomIs(array('Propertydefinition', 'Magicmethod'))
             )
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('OVERWRITE')
                      ->atomIs('Propertydefinition')
             )
             ->outIs('OBJECT')
             ->atomIs(array('Variableobject', 'This'), self::WITHOUT_CONSTANTS)
             ->inIs('DEFINITION') // Good enough for This
             ->optional(          // For arguments
                 $this->side()
                      ->atomIs('Parameter', self::WITHOUT_CONSTANTS)
                      ->outIs('TYPEHINT')
                      ->inIs('DEFINITION')
             )
             ->optional(  // for local variables
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIs('New')
                      ->outIs('NEW')
                      ->inIs('DEFINITION')
             )

            // In case we are in an interface
             ->optional(
                 $this->side()
                      ->atomIs('Interface', self::WITHOUT_CONSTANTS)
                      ->outIs('DEFINITION')
                      ->inIs('IMPLEMENTS')
             )

             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('MAGICMETHOD')
             ->atomIs('Magicmethod')
             ->outIs('NAME')
             ->codeIs('__get', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')

             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // link to __set
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
             ->is('isModified', true)
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->atomIs(array('Propertydefinition'))
             )
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('OVERWRITE')
                      ->atomIs('Propertydefinition')
             )
             ->outIs('OBJECT')
             ->atomIs(array('Variableobject', 'This'), self::WITHOUT_CONSTANTS)
             ->inIs('DEFINITION') // Good enough for This
             ->optional(          // For arguments
                 $this->side()
                      ->atomIs('Parameter', self::WITHOUT_CONSTANTS)
                      ->outIs('TYPEHINT')
                      ->inIs('DEFINITION')
             )

            // In case we are in an interface
             ->optional(
                 $this->side()
                      ->atomIs('Interface', self::WITHOUT_CONSTANTS)
                      ->outIs('DEFINITION')
                      ->inIs('IMPLEMENTS')
             )

             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('MAGICMETHOD')
             ->outIs('NAME')
             ->codeIs('__set', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // isset($this->a)
        // links to __isset
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->atomIs(array('Propertydefinition', 'Magicmethod'))
             )
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('OVERWRITE')
                      ->atomIs('Propertydefinition')
             )
             ->inIs('ARGUMENT')
             ->atomIs('Isset')
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs(array('Variableobject', 'This'), self::WITHOUT_CONSTANTS)
             ->optional(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('TYPEHINT')
             )
             ->inIs('DEFINITION')
             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('MAGICMETHOD')
             ->outIs('NAME')
             ->codeIs('__isset', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // unset($this->a)
        // links to __unset
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->atomIs(array('Propertydefinition', 'Magicmethod'))
             )
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('OVERWRITE')
                      ->atomIs('Propertydefinition')
             )
             ->inIs('ARGUMENT')
             ->atomIs('Unset')
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs(array('Variableobject', 'This'), self::WITHOUT_CONSTANTS)
             ->optional(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('TYPEHINT')
             )
             ->inIs('DEFINITION')
             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('MAGICMETHOD')
             ->outIs('NAME')
             ->codeIs('__unset', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // unset() $this->a
        // links to __unset
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->atomIs(array('Propertydefinition', 'Magicmethod'))
             )
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('OVERWRITE')
                      ->atomIs('Propertydefinition')
             )
             ->inIs('CAST')
             ->atomIs('Cast')
             ->tokenIs('T_UNSET_CAST')
             ->back('first')

              ->outIs('OBJECT')
             ->atomIs(array('Variableobject', 'This'), self::WITHOUT_CONSTANTS)
             ->optional(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('TYPEHINT')
             )
             ->inIs('DEFINITION')
             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('MAGICMETHOD')
             ->outIs('NAME')
             ->codeIs('__unset', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // links to __invoke
        // @todo : some calls to __invoke are missing! parameters, typed variables
        // $a = new X; $a();
        $this->atomIs('Magicmethod', self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->fullcodeIs('__invoke', self::CASE_INSENSITIVE)
             ->back('first')

             ->inIs('MAGICMETHOD')
             ->outIs('DEFINITION')
             ->inIs('TYPEHINT')
             ->outIsIE('PPP') /// case for properties
             ->outIs('DEFINITION')
             ->inIs('NAME')
             ->atomIs('Functioncall')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addEFrom('DEFINITION', 'first');
        $this->prepareQuery();

        // $this($a);
        $this->atomIs('Magicmethod', self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->codeIs('__invoke', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->back('first')

             ->inIs('MAGICMETHOD')
             ->outIs('DEFINITION')
             ->atomIs('This')
             ->inIs('NAME')
             ->atomIs('Functioncall')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addEFrom('DEFINITION', 'first');
        $this->prepareQuery();

        // links to __string
        $this->atomIs('Magicmethod', self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->fullcodeIs('__tostring', self::CASE_INSENSITIVE)
             ->back('first')

             ->inIs('MAGICMETHOD')
             ->outIs('DEFINITION')
             ->inIs('TYPEHINT')
             ->outIs('DEFINITION')
             ->hasIn('CONCAT')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addEFrom('DEFINITION', 'first');
        $this->prepareQuery();

        // @todo add link from echo/print
    }
}

?>
