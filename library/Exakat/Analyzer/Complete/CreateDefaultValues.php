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

use Exakat\Query\DSL\FollowParAs;

class CreateDefaultValues extends Complete {
    public function dependsOn(): array {
        return array('Complete/OverwrittenProperties',
                     'Variables/Selftransform',
                    );
    }

    public function analyze(): void {
        // Link initial values for containers
        $this->atomIs(array('Variabledefinition',
                            'Virtualproperty',
                            'Propertydefinition',
                            'Parametername',
                            ), self::WITHOUT_CONSTANTS)
             ->outIsIE('NAME')
             ->savePropertyAs('fullcode', 'left')
             ->back('first')

             ->outIs('DEFINITION')
             ->analyzerIsNot('Variables/SelfTransform')
             ->inIs('LEFT')
             ->atomIs('Assignation', self::WITHOUT_CONSTANTS)
             ->codeIs(array('=', '??='), self::TRANSLATE, self::CASE_SENSITIVE) // can't accept .=, +=, etc.

             // doesn't use self : $a = $a + 1 is not a default value
             ->outIs('RIGHT')
             ->followParAs(FollowParAs::FOLLOW_PARAS_ONLY)

             // 'Variableobject', 'Variablearray' are never on the right side of an assignation (not directly)
             ->not(
                 $this->side()
                      ->atomIs('Variable')
                      ->inIs('DEFINITION')
                      ->isEqual('first')
             )

             ->not(
                 $this->side()
                      ->inIs('DEFAULT')
                      ->isEqual('first')
             )

             ->addEFrom('DEFAULT', 'first');
        $this->prepareQuery();

        // With comparisons
        $this->atomIs(array('Variabledefinition',
                            'Virtualproperty',
                            'Propertydefinition',
                            'Parametername',
                            ), self::WITHOUT_CONSTANTS)
             ->outIs('DEFINITION')
             ->inIs(array('LEFT', 'RIGHT'))
             ->atomIs('Comparison', self::WITHOUT_CONSTANTS)
             ->codeIs(array('==', '!=', '===', '!==', ), self::TRANSLATE, self::CASE_SENSITIVE)
             ->outIs(array('LEFT', 'RIGHT'))
             ->atomIs(array('Integer', 'String'), self::WITH_CONSTANTS)
             ->not(
                 $this->side()
                      ->inIs('DEFAULT')
                      ->isEqual('first')
             )
             ->addEFrom('DEFAULT', 'first');
        $this->prepareQuery();

        // With switch/match
        $this->atomIs(array('Variabledefinition',
                            'Virtualproperty',
                            'Propertydefinition',
                            'Parametername',
                            ), self::WITHOUT_CONSTANTS)
             ->outIs('DEFINITION')
             ->inIs('CONDITION')
             ->atomIs(self::SWITCH_ALL)
             ->outIs('CASES')
             ->outIs('EXPRESSION')
             ->atomIs('Case')
             ->outIs('CASE')
             ->atomIs(array('Integer', 'String'), self::WITH_CONSTANTS)
             ->not(
                 $this->side()
                      ->inIs('DEFAULT')
                      ->isNotEqual('first')
             )
             ->addEFrom('DEFAULT', 'first');
        $this->prepareQuery();

        // With foreach($a as $v)
        // With foreach($a as $k => $v)
        // This is done with Complete/CreateForeachDefault

        // propagate virtualproperties to original definition
        // This one must be the final of this analysis
        $this->atomIs(array('Propertydefinition'), self::WITHOUT_CONSTANTS)
             ->inIs('OVERWRITE')
             ->outIs('DEFAULT')
             ->not(
                 $this->side()
                      ->inIs('DEFAULT')
                      ->isNotEqual('first')
             )
             ->atomIsNot('Void')
             ->addEFrom('DEFAULT', 'first');
        $this->prepareQuery();

        // for global values
        $this->atomIs(array('Virtualglobal'), self::WITHOUT_CONSTANTS)
             ->outIs('DEFINITION')
             ->inIs('DEFINITION')
             ->atomIs('Variabledefinition')
             ->outIs('DEFAULT')
             ->addEFrom('DEFAULT', 'first');
        $this->prepareQuery();
    }
}

?>
