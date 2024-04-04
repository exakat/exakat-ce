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

class MakeClassConstantDefinition extends Complete {
    public function dependsOn(): array {
        return array('Complete/SetParentDefinition',
                     'Complete/CreateDefaultValues',
                    );
    }

    public function analyze(): void {
        // X::Constante -> class X { const Constante}
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
             ->hasNoIn('DEFINITION')
             ->outIs('CONSTANT')
             ->savePropertyAs('code', 'name')
             ->back('first')
             ->outIs('CLASS')
             ->atomIs(array('Identifier', 'Nsname', 'Self', 'Static', 'This'), self::WITHOUT_CONSTANTS)
             ->inIs('DEFINITION')
             ->atomIs(array('Class', 'Classanonymous', 'Interface'), self::WITHOUT_CONSTANTS)
             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('CONST')
             ->atomIs('Const', self::WITHOUT_CONSTANTS)
             ->outIs('CONST')
             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // static::Constante -> class { const Constante}
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('CLASS')
              ->atomIs('Static', self::WITHOUT_CONSTANTS)

              ->inIs('DEFINITION')
              ->atomIs(array('Class', 'Classanonymous', 'Interface'), self::WITHOUT_CONSTANTS)
              ->goToAllChildren(self::EXCLUDE_SELF)
              ->outIs('CONST')
              ->atomIs('Const', self::WITHOUT_CONSTANTS)
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // X::Constante -> class X { const Constante} non-private
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('CLASS')
              ->atomIs(array('Identifier', 'Nsname', 'Self', 'Static', 'Parent', 'This'), self::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullnspath', 'classe')
              ->inIs('DEFINITION')
              ->atomIs(array('Class', 'Classanonymous', 'Interface'), self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::EXCLUDE_SELF)
              ->outIs('CONST')
              ->atomIs('Const', self::WITHOUT_CONSTANTS)
              ->isNot('visibility', 'private')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // parent::Constante -> class { const Constante}
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('CLASS')
              ->atomIs('Parent', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullnspath', 'classe')
              ->inIs('DEFINITION')
              ->atomIs(array('Class', 'Classanonymous', 'Interface'), self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->atomIs('Const', self::WITHOUT_CONSTANTS)
              ->isNot('visibility', 'private')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // x $a; $a::Constante -> class x { const Constante}
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('CLASS')
              ->atomIs(array('Variable', 'Member', 'Staticproperty'))
              ->goToTypehint()
              ->inIs('DEFINITION')
              ->atomIs(array('Class', 'Classanonymous', 'Interface'), self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->atomIs('Const', self::WITHOUT_CONSTANTS)
              ->isNot('visibility', 'private')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // $a = new a; $a::Constante -> class x { const Constante}
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('CLASS')
              ->atomIs(array('Variable', 'Member', 'Staticproperty'))
              ->inIs('DEFINITION')
              ->outIs('DEFAULT')
              ->atomIs('New')
              ->outIs('NEW')
              ->inIs('DEFINITION')
              ->atomIs(array('Class', 'Classanonymous', 'Interface'), self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->atomIs('Const', self::WITHOUT_CONSTANTS)
              ->isNot('visibility', 'private')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // class x { echo self::A; } class b extends a { const A = 1;}
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('CLASS')
              ->atomIs(array('Static', 'This'))
              ->inIs('DEFINITION')
              ->atomIs(array('Class', 'Classanonymous', 'Interface'), self::WITHOUT_CONSTANTS)
              ->is('abstract', true)
              ->goToAllChildren(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->atomIs('Const', self::WITHOUT_CONSTANTS)
              ->isNot('visibility', 'private')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first');
        $this->prepareQuery();
    }
}

?>
