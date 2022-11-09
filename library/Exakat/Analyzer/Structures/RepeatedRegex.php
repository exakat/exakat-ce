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

class RepeatedRegex extends Analyzer {
    public function analyze(): void {
        $functionsList = array('\preg_match',
                               '\preg_filter',
                               '\preg_grep',
                               '\preg_replace',
                               '\preg_match_all',
                               '\preg_split',
                               '\preg_replace_callback_array',
                               '\preg_replace_callback',
                              );

        $this->atomFunctionIs($functionsList)
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->hasNoOut('CONCAT')
             ->raw(<<<'GREMLIN'
groupCount("m").by("code").cap("m").next().findAll{ a,b -> b > 1}.keySet()
GREMLIN
             );
        $repeatedRegex = $this->rawQuery()->toArray();

        if (empty($repeatedRegex)) {
            return;
        }

        // regex
        $this->atomFunctionIs($functionsList)
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->hasNoOut('CONCAT')
             ->codeIs($repeatedRegex, self::NO_TRANSLATE)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
