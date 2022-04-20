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

class NextMonthTrap extends Analyzer {
    public function analyze(): void {
        // strtotime('+ 1 month')
        $this->atomFunctionIs('\strtotime')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs(self::STRINGS_LITERALS, self::WITH_CONSTANTS)
             ->regexIs('fullcode', '(\\\\+|-|\\\\\$)[0-9a-zA-Z_ ]+month')
             ->back('first');
        $this->prepareQuery();

        // strtotime('+ 1 month')
        $this->atomFunctionIs('\strtotime')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs(self::STRINGS_LITERALS, self::WITH_CONSTANTS)
             ->regexIs('noDelimiter', '(?i)(?<!of )next month')
             ->back('first');
        $this->prepareQuery();

        // new Datetime('+ 1 month')
        $this->atomIs('New')
             ->outIs('NEW')
             ->fullnspathIs(array('\\datetime', '\\datetimeimmutable'))
             ->outWithRank('ARGUMENT', 0)
             ->atomIs(self::STRINGS_LITERALS, self::WITH_CONSTANTS)
             ->regexIs('fullcode', '(\\\\+|-|\\\\\$)[0-9a-zA-Z_ ]+month')
             ->back('first');
        $this->prepareQuery();

        // strtotime('+ 1 month')
        $this->atomIs('New')
             ->outIs('NEW')
             ->fullnspathIs(array('\\datetime', '\\datetimeimmutable'))
             ->outWithRank('ARGUMENT', 0)
             ->atomIs(self::STRINGS_LITERALS, self::WITH_CONSTANTS)
             ->regexIs('noDelimiter', '(?i)(?<!of )next month')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
