<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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
    protected $classes = array();

    public function setClasses($classes): void {
        $this->classes = $classes;
    }

    public function analyze(): void {
        $classes =  makeFullNsPath($this->classes);

        // New X();
        $this->atomIs(self::NEW_CALLS)
             ->hasNoIn('NAME')
             ->has('fullnspath')
             ->fullnspathIs($classes);
        $this->prepareQuery();

        $this->atomIs(array('Staticmethodcall', 'Staticproperty', 'Staticconstant', 'Staticclass'))
             ->outIs('CLASS')
             ->atomIs(self::CONSTANTS_ALL)
             ->fullnspathIs($classes);
        $this->prepareQuery();

        // Typehint (return and argument), catch, instanceof, classes
        $this->atomIs(array('Identifier', 'Nsname'))
             ->hasIn(array('TYPEHINT', 'RETURNTYPE', 'EXTENDS', 'IMPLEMENTS', 'CLASS'))
             ->fullnspathIs($classes)
             ->analyzerIsNot('self');
        $this->prepareQuery();

        $this->atomIs('Classalias')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->noDelimiterIs($classes);
        $this->prepareQuery();
    }
}

?>
