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


namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class UndefinedProperty extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/CreateMagicProperty',
                     'Classes/HasMagicProperty',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                     'Complete/SetClassPropertyDefinitionWithTypehint',
                     'Complete/VariableTypehint',
                     'Complete/IsPhpStructure',
                     'Complete/IsStubStructure',
                     'Classes/ExtendsStdclass',
                    );
    }

    public function analyze(): void {
        // Check proeprties with local definition
        $this->atomIs(self::PROPERTIES)

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
             ->analyzerIsNot('self')
             ->hasNoIn('DEFINITION') // No definition found

             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->isNot('isStub', true)

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
             ->analyzerIsNot('self')
             ->hasNoIn('DEFINITION')
             ->outIs(array('OBJECT', 'CLASS'))
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('TYPEHINT')
             ->inIs('DEFINITION')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
