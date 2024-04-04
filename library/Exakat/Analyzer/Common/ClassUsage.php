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


namespace Exakat\Analyzer\Common;

use Exakat\Analyzer\Analyzer;

class ClassUsage extends Analyzer {
    protected array $classes = array();

    public function dependsOn(): array {
        return array('Complete/ReturnTypehint',
                    );
    }

    public function setClasses(array $classes): void {
        $this->classes = $classes;
    }

    public function analyze(): void {
        $classes =  makeFullNsPath($this->classes);

        // New X();
        $this->atomIs(self::NEW_CALLS)
             ->hasNoIn('NAME')
             ->fullnspathIs($classes);
        $this->prepareQuery();

        $interfaces = $this->readStubs('getInterfaceList');
        $traits = $this->readStubs('getTraitList');
        $list = array_diff($classes, $interfaces, $traits);

        // Typehint (return and argument), catch, instanceof, ::class, classes with extends
        $this->atomIs(array('Identifier', 'Nsname'))
             ->hasIn(array('TYPEHINT', 'RETURNTYPE', 'EXTENDS', 'CLASS')) // NOT IMPLEMENT
             ->fullnspathIs($list)
             ->isNot('extra', true)
             ->analyzerIsNot('self');
        $this->prepareQuery();

        // class_alias('a', $b);
        $this->atomIs('Classalias')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->analyzerIsNot('self')
             ->noDelimiterIs($list);
        $this->prepareQuery();
    }
}

?>
