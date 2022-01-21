<?php
/*
 * Copyright 2012-2021 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

class ChecksPropertyExistence extends Analyzer {
    public function dependsOn() : array {
        return array('Complete/VariableTypehint',
                     'Classes/HasMagicProperty',
                    );
    }

    public function analyze() : void {
        // isset($this->property)
        $this->atomIs('Isset')
             ->outIs('ARGUMENT')
             ->atomIs(array('Member', 'Staticproperty'))
             ->outIs(array('OBJECT', 'CLASS'))
             ->inIs('DEFINITION')
             ->atomIs('Class')

             // no __get/__set
             ->analyzerIsNot('Classes/HasMagicProperty')

            // Do not extends stdclass 
            ->not(
                $this->side()
                     ->goToClass()
                     ->extending(array('\\stdclass'))
            )
        
             ->back('first');
        $this->prepareQuery();
        
        // isset($x->property)
        $this->atomIs('Isset')
             ->outIs('ARGUMENT')
             ->atomIs(array('Member', 'Staticproperty'))
             ->outIs(array('OBJECT', 'CLASS'))
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('TYPEHINT')
             ->fullnspathIsNot('\\stdclass')
             ->inIs('DEFINITION')

             ->atomIs('Class')
             // no __get/__set
             ->analyzerIsNot('Classes/HasMagicProperty')

            // Do not extends stdclass 
            ->not(
                $this->side()
                     ->goToClass()
                     ->goToAllParents(self::INCLUDE_SELF)
                     ->outIs('EXTENDS')
                     ->is('fullnspath', array('\\stdclass'))
            )
        
             ->back('first');
        $this->prepareQuery();

        // property_exists($this->property)
        $this->atomFunctionIs('\\property_exists')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('This')
             ->inIs('DEFINITION')

             ->atomIs('Class')
             // no __get/__set
             ->analyzerIsNot('Classes/HasMagicProperty')

            // Do not extends stdclass 
            ->not(
                $this->side()
                     ->goToClass()
                     ->goToAllParents(self::INCLUDE_SELF)
                     ->outIs('EXTENDS')
                     ->is('fullnspath', array('\\stdclass'))
            )
            ->back('first');
        $this->prepareQuery();

        // property_exists($x->property)
        $this->atomFunctionIs('\\property_exists')
             ->outWithRank('ARGUMENT', 0)
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('TYPEHINT')
             ->fullnspathIsNot('\\stdclass')
             ->inIs('DEFINITION')

             ->atomIs('Class')
             // no __get/__set
             ->analyzerIsNot('Classes/HasMagicProperty')

            // Do not extends stdclass 
            ->not(
                $this->side()
                     ->goToClass()
                     ->goToAllParents(self::INCLUDE_SELF)
                     ->outIs('EXTENDS')
                     ->is('fullnspath', array('\\stdclass'))
            )
            ->back('first');
        $this->prepareQuery();
    }
}

?>
