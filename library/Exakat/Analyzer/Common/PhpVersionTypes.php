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

class PhpVersionTypes extends Analyzer {
    protected array $types = array();

    public function analyze(): void {
        if (empty($this->types)) {
            return;
        }

        $atoms = array_merge(self::STATIC_NAMES, array('Scalartypehint'));

        // function foo() : null | stringable
        $this->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('self')
             ->outIs('RETURNTYPE')
             ->outIsIE('RETURNTYPE')
             ->atomIs($atoms)
             ->fullnspathIs($this->types)
             ->fullcodeIsNot('?')
             ->back('first');
        $this->prepareQuery();

        // function foo( null | stringable $x) {}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('self')
             ->outIs('ARGUMENT')
             ->outIs('TYPEHINT')
             ->outIsIE('TYPEHINT')
             ->atomIs($atoms)
             ->fullnspathIs($this->types)
             ->fullcodeIsNot('?')
             ->back('first');
        $this->prepareQuery();

        // class x { private a|null $p; }
        $this->atomIs('Ppp')
             ->analyzerIsNot('self')
             ->outIs('TYPEHINT')
             ->outIsIE('TYPEHINT')
             ->atomIs($atoms)
             ->fullnspathIs($this->types)
             ->fullcodeIsNot('?')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
