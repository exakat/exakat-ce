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

namespace Exakat\Analyzer\Complete;

class SolveTraitMethods extends Complete {
    public function analyze(): void {
        // use a,b {m as c};
        // use a,b {a::m as c};
        // use a\d\e,b {a\d\e::m as c};
        $this->atomIs('Usetrait', self::WITHOUT_CONSTANTS)
              ->outIs('BLOCK')
              ->outIs('EXPRESSION')
              ->atomIs('As', self::WITHOUT_CONSTANTS)

              ->outIs('AS')
              ->atomIs('Identifier', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'methode_target')
              ->inIs('AS')

              ->outIs('NAME')
              ->outIsIE('METHOD')
              ->atomIs(array('Identifier', 'Nsname'), self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'methode')
              ->back('first')
              ->outIs('USE')
              ->inIs('DEFINITION')
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'methode', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('definition')

              ->back('first')
              ->inIs('USE')
              ->atomIs(array('Class', 'Trait'))
              ->outIs('METHOD')
              ->outIs('DEFINITION')
              ->as('results')
              ->outIs('METHOD')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'methode_target', self::CASE_INSENSITIVE)

              ->back('results')
              ->hasNoLinkYet('DEFINITION', 'definition')
              ->addEFrom('DEFINITION', 'definition');
        $this->prepareQuery();

        // use a,b {a::m insteadof c};
        $this->atomIs('Usetrait', self::WITHOUT_CONSTANTS)
              ->outIs('BLOCK')
              ->outIs('EXPRESSION')
              ->atomIs('Insteadof', self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->outIs('METHOD')
              ->savePropertyAs('lccode', 'methode')
              ->inIs('METHOD')
              ->outIs('CLASS')
              ->inIs('DEFINITION')
              ->atomIs('Trait')
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'methode', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('definition')

              ->back('first')
              ->inIs('USE')
              ->atomIs(array('Class', 'Trait'))
              ->goToAllChildren(self::INCLUDE_SELF)
              ->outIs('METHOD')
              ->outIs('DEFINITION')
              ->as('results')
              ->outIs('METHOD')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'methode')

              ->back('results')
              ->hasNoLinkYet('DEFINITION', 'definition')
              ->addEFrom('DEFINITION', 'definition');
        $this->prepareQuery();
    }
}

?>
