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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class TimestampDifference extends Analyzer {
    public function analyze(): void {
        // All kinds of addition are allowed here :
        // microtime() - 1 or microtime() + -3

        // microtime(true) - microtime(true)
        $this->atomIs('Addition')
             ->outIs(array('LEFT', 'RIGHT'))
             ->functioncallIs(array('\\time', '\\microtime'))
             ->back('first');
        $this->prepareQuery();

        // date('U') - $d
        $this->atomIs('Addition')
             ->outIs(array('LEFT', 'RIGHT'))
             ->atomIs('Functioncall', self::WITH_VARIABLES)
             ->fullnspathIs('\\date')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->noDelimiterIs('U')
             ->back('first');
        $this->prepareQuery();

        // dateTime->format('U') - $d
        $this->atomIs('Addition')
             ->outIs(array('LEFT', 'RIGHT'))
             ->atomIs('Methodcall', self::WITH_VARIABLES)
             ->as('method')
             ->outIs('METHOD')
             ->codeIs('format', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->noDelimiterIs('U')

             // No check on class yet
             // it should accepte datetime and datetimeimmutable

             ->back('first');
        $this->prepareQuery();

        // Note : date('U') is the exact call to date.

    }
}

?>
