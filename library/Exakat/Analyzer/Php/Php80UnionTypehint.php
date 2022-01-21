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

namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;

class Php80UnionTypehint extends Analyzer {
    protected $phpVersion = '8.0+';

    public function analyze(): void {
        // return values A | B
        $this->atomIs(self::FUNCTIONS_ALL)
             ->is('typehint', 'or')
             ->filter(
                $this->side()
                     ->outIs('RETURNTYPE')
                     ->fullcodeIsNot('?')
                     ->raw('count().is(gt(1))')
             );
        $this->prepareQuery();

        // arguments A | B
        $this->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('self')
             ->outIs('ARGUMENT')
             ->is('typehint', 'or')
             ->filter(
                $this->side()
                     ->outIs('TYPEHINT')
                     ->fullcodeIsNot('?')
                     ->raw('count().is(gt(1))')
             );
        $this->prepareQuery();

        // properties A | B
        $this->atomIs('Ppp')
             ->is('typehint', 'or')
             ->filter(
                $this->side()
                     ->outIs('TYPEHINT')
                     ->fullcodeIsNot('?')
                     ->raw('count().is(gt(1))')
             );

        $this->prepareQuery();
    }
}

?>
