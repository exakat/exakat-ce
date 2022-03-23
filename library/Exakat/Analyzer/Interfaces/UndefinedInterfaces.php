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


namespace Exakat\Analyzer\Interfaces;

use Exakat\Analyzer\Analyzer;

class UndefinedInterfaces extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // interface used in a class
        $this->atomIs(self::CLASSES_ALL)
             ->outIs('IMPLEMENTS')
             ->hasNoIn('DEFINITION')
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->isNot('isStub', true);
        $this->prepareQuery();

        // interface extending another interface
        $this->atomIs('Interface')
             ->outIs('EXTENDS')
             ->hasNoIn('DEFINITION')
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->isNot('isStub', true);
        $this->prepareQuery();

        // interface used in a instanceof nor a Typehint but not defined
        $this->atomIs('Instanceof')
             ->outIs('CLASS')
             ->atomIsNot(array('Self', 'Parent'))
             ->has('fullnspath')
             ->noClassDefinition()
             ->noInterfaceDefinition()
             ->isNotIgnored()
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->isNot('isStub', true);
        $this->prepareQuery();

        // types, (typeints or returntype)
        $this->atomIs(self::CONSTANTS_ALL)
             ->hasIn(self::TYPE_LINKS)
             ->atomIsNot(array('Self', 'Parent'))
             ->has('fullnspath')
             ->noClassDefinition()
             ->noInterfaceDefinition()
             ->noUseDefinition()
             ->has('line')
             ->isNotIgnored()
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->isNot('isStub', true);
        $this->prepareQuery();
    }
}

?>
