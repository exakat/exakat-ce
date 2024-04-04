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


namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class UndefinedProperty extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/CreateMagicProperty',
                     'Classes/HasMagicProperty',
                     'Complete/SetClassPropertyDefinitionWithTypehint',
                     'Complete/IsPhpStructure',
                     'Complete/IsStubStructure',
                     'Classes/ExtendsStdclass',
                     'Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // Check properties with local definition
        $this->atomIs(self::PROPERTIES)
            ->hasNoTrait()

            // static property names
            ->not(
                $this->side()
                     ->atomIs('Member')
                     ->outIs('MEMBER')
                     ->tokenIsNot('T_STRING')
            )

            // do not have __get/__set set up (trait or parents or else)
            ->not(
                $this->side()
                     ->goToClass()
                     ->analyzerIs('Classes/HasMagicProperty')
            )

            // Do not extends stdclass
            ->not(
                $this->side()
                     ->goToClass()
                     ->analyzerIs('Classes/ExtendsStdclass')
            )

            // Do not use  #[allowdynamicproperties ]
            ->hasNotAttribute('\\allowdynamicproperties')

             // not a property in a parent class in the code
             ->inIs('DEFINITION')
             ->atomIs('Virtualproperty')
             ->not(
                 $this->side()
                      ->outIs('OVERWRITE')
                      ->atomIs('Propertydefinition')
                      ->inIs('PPP')
                      ->isNot('visibility','private')
             )

             // Not a property in a included component
             ->back('first');
        $this->prepareQuery();

        // Check on properties with external definition
        // relies on isPHP, isStub, isExt
        $this->atomIs(self::PROPERTIES)
             ->hasNoTrait()
             ->analyzerIsNot('self')
             ->hasNoIn('DEFINITION') // No definition found

            ->not(
                $this->side()
                     ->atomIs('Member')
                     ->outIs('MEMBER')
                     ->tokenIsNot('T_STRING')
            )

            ->not(
                $this->side()
                     ->atomIs('Staticproperty')
                     ->outIs('MEMBER')
                     ->tokenIsNot('T_VARIABLE')
            )

             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->isNot('isStub', true)

            // The supporting object has No Definition
            ->not(
                $this->side()
                     ->outIs('OBJECT')
                     ->inIs('DEFINITION')
                     ->hasNoOut(array('TYPEHINT', 'RETURNTYPE')) // This might be a method too
            )

            // static property names
            ->not(
                $this->side()
                     ->atomIs('Member')
                     ->outIs('MEMBER')
                     ->atomIsNot('Name')
            )

             ->not( // No dynamic elements
                 $this->side()
                      ->outIs('OBJECT')
                      ->inIs('DEFINITION')
                      ->atomIs('Virtualproperty')
             )

             ->not( // No dynamic elements
                 $this->side()
                      ->atomIs('Member')
                      ->outIs('MEMBER')
                      ->tokenIsNot('T_STRING')
             )
             ->not( // No dynamic elements
                 $this->side()
                      ->atomIs('Staticproperty')
                      ->outIs('MEMBER')
                      ->tokenIsNot('T_VARIABLE')
             )

             // Not a property in a included component
             ->back('first');
        $this->prepareQuery();

        // case of public access
        $this->atomIs(self::PROPERTIES)
             ->hasNoTrait()
             ->analyzerIsNot('self')
             ->hasNoIn('DEFINITION')
             ->outIs(array('OBJECT', 'CLASS'))
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('TYPEHINT')
             ->inIs('DEFINITION')
             ->isNot('isPhp', true)
             ->isNot('isStub', true)
             ->isNot('isExt', true)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
