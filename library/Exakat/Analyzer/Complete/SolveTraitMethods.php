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

namespace Exakat\Analyzer\Complete;

class SolveTraitMethods extends Complete {
    public function analyze(): void {
        // use a,b {a::m as c};
        $this->atomIs('Usetrait', self::WITHOUT_CONSTANTS)
              ->outIs('BLOCK')
              ->outIs('EXPRESSION')
              ->atomIs('As', self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->atomIs('Staticmethod', self::WITHOUT_CONSTANTS)
              ->as('results')
              ->tokenIs('T_STRING')
              ->savePropertyAs('lccode', 'methode')
              ->back('first')
              ->outIs('USE')
              ->inIs('DEFINITION')
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'methode', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->addETo('DEFINITION', 'results');
        $this->prepareQuery();

        // use a\d\e,b {a\d\e::m as c};
        $this->atomIs('Usetrait', self::WITHOUT_CONSTANTS)
              ->outIs('BLOCK')
              ->outIs('EXPRESSION')
              ->atomIs('As', self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->as('results')
              ->atomIs('Nsname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'methode')
              ->back('first')
              ->outIs('USE')
              ->inIs('DEFINITION')
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'methode', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->addETo('DEFINITION', 'results');
        $this->prepareQuery();

        // use a,b {a::m insteadof c};
        $this->atomIs('Usetrait', self::WITHOUT_CONSTANTS)
              ->outIs('BLOCK')
              ->outIs('EXPRESSION')
              ->atomIs('Insteadof', self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->as('results')
              ->outIs('NAME')
              ->savePropertyAs('lccode', 'methode')
              ->back('results')
              ->outIs('CLASS')
              ->inIs('DEFINITION')
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'methode', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->addETo('DEFINITION', 'results');
        $this->prepareQuery();
    }
}

?>
