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


namespace Exakat\Analyzer\Functions;

use Exakat\Analyzer\Analyzer;

class WrongOptionalParameter extends Analyzer {
    public function analyze(): void {
        // function foo($a, $b = 2, $c) {}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->as('x')
             ->outIs('DEFAULT')
             ->atomIsNot('Void')
             ->hasNoIn('RIGHT')

             ->back('x')
            // with typehint, but nullable (so, optional)
            // valid since PHP 8.0
             ->not(
                $this->side()
                     ->outIs('TYPEHINT')
                     ->atomIsNot(array('Void', 'Null'))
                     ->inIs('TYPEHINT')
                     ->outIs('DEFAULT')
                     ->atomIs('Null')
                     ->hasNoIn('RIGHT')
             )

             ->nextSibling('ARGUMENT')
             ->outIsIE('PPP')
             ->isNot('variadic', true)
             ->outIs('DEFAULT')
             ->atomIs('Void')
             ->hasNoIn('RIGHT')

             ->back('first');
        $this->prepareQuery();

        // function __construct($a, $b = 2, $c) {}
        $this->atomIs('Magicmethod')
             ->outIs('ARGUMENT')
             ->atomIs('Ppp')
             ->as('x')
             ->outIs('PPP')
             ->outIs('DEFAULT')
             ->atomIsNot('Void')
             ->hasNoIn('RIGHT')

             ->back('x')
            // with typehint, but nullable (so, optional)
            // valid since PHP 8.0
             ->not(
                $this->side()
                     ->outIs('TYPEHINT')
                     ->atomIsNot(array('Void', 'Null'))
                     ->inIs('TYPEHINT')
                     ->outIs('PPP')
                     ->outIs('DEFAULT')
                     ->atomIs('Null')
                     ->hasNoIn('RIGHT')
             )

             ->nextSibling('ARGUMENT')
             // case of promoted properties
             ->outIsIE('PPP')
             ->outIs('DEFAULT')
             ->atomIs('Void')
             ->hasNoIn('RIGHT')

             ->back('first');
        $this->prepareQuery();
    }
}

?>
