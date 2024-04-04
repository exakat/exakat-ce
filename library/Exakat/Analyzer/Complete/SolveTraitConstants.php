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

class SolveTraitConstants extends Complete {
    protected string $phpVersion = '8.2+';

    public function analyze(): void {
        // Directly from the class : already handled
        // self::A,

        // Directly from the trait's class
        // self::A,
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
             ->hasNoIn('DEFINITION')
             ->outIs('CONSTANT')
             ->savePropertyAs('code', 'name')
             ->back('first')

             ->outIs('CLASS')
             ->inIs('DEFINITION') // @todo : more cases? $p with type, for example
             ->optional(
                 $this->side()
                      ->outIs('TYPEHINT') // @todo : case with intersectional and union ? And DNF
                      ->inIs('DEFINITION')
             )
             ->atomIs('Class')
             ->outIs('USE')
             ->outIs('USE')
             ->inIs('DEFINITION')
             ->atomIs('Trait')

             ->outIs('CONST')
             ->outIs('CONST')
             ->as('results')

             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
             ->back('results')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Directly from the parent's class : already handled
        // self::A,

        return;
        // use a,b {m as c};
        // use a,b {a::m as c};
        // use a\d\e,b {a\d\e::m as c};
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
             ->hasNoIn('DEFINITION')
             ->outIs('CONSTANT')
             ->savePropertyAs('code', 'name')
             ->back('first')

             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->atomIs('Class')
             ->outIs('USE')
             ->as('theTrait')

              ->outIs('BLOCK')
              ->outIs('EXPRESSION')
              ->atomIs('As', self::WITHOUT_CONSTANTS)

              ->outIs('AS')
              ->samePropertyAs('code', 'name')
              ->inIs('AS')

              ->outIs('NAME')
              ->savePropertyAs('code', 'constant_target')

              ->back('theTrait')
              ->outIs('USE')
              ->inIs('DEFINITION')
              ->outIs('CONST')
              ->outIs('CONST')
              ->as('definition')
              ->outIs('NAME')
              ->samePropertyAs('code', 'constant_target', self::CASE_SENSITIVE)
              ->back('definition')

              ->addEFrom('DEFINITION', 'first');
        $this->prepareQuery();
    }
}

?>
