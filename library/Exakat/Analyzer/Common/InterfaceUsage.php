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


namespace Exakat\Analyzer\Common;

use Exakat\Analyzer\Analyzer;

class InterfaceUsage extends Analyzer {
    protected array $interfaces = array();

    public function setInterfaces(array $interfaces): void {
        $this->interfaces = $interfaces;
    }

    public function analyze(): void {
        $interfaces =  makeFullNsPath($this->interfaces);

        $this->atomIs('Class')
             ->outIs('IMPLEMENTS')
             ->analyzerIsNot('self')
             ->atomIs(self::STATIC_NAMES)
             ->fullnspathIs($interfaces);
        $this->prepareQuery();

        $this->atomIs('Interface')
             ->outIs('EXTENDS')
             ->analyzerIsNot('self')
             ->atomIs(self::STATIC_NAMES)
             ->fullnspathIs($interfaces)
             ->analyzerIsNot('self');
        $this->prepareQuery();

        // @todo : shall skip traits and enums too ?
        $classes = $this->readStubs('getClassList');
        $classes = $this->reduceStubs($classes, 'getCalledClasses', self::REDUCE_VALUE);

        $this->atomIs(array('Instanceof', 'Catch', 'Staticconstant', 'Staticclass'))
             ->outIs('CLASS')
             ->analyzerIsNot('self')
             ->atomIs(self::STATIC_NAMES)
             ->fullnspathIs($interfaces)
             ->fullnspathIsNot($classes);
        $this->prepareQuery();

        $this->atomIs('Parameter')
             ->outIs('TYPEHINT')
             ->analyzerIsNot('self')
             ->has('line')
             ->atomIs(self::STATIC_NAMES)
             ->tokenIsNot(array('T_ARRAY', 'T_CALLABLE'))
             ->fullnspathIs($interfaces)
             ->fullnspathIsNot($classes);
        $this->prepareQuery();

        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('RETURNTYPE')
             ->analyzerIsNot('self')
             ->has('line')
             ->atomIs(self::STATIC_NAMES)
             ->tokenIsNot(array('T_ARRAY', 'T_CALLABLE'))
             ->fullnspathIs($interfaces)
             ->fullnspathIsNot($classes);
        $this->prepareQuery();
    }
}

?>
