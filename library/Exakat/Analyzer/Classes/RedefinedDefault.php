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
namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class RedefinedDefault extends Analyzer {
    public function analyze(): void {
        // class x { private $y = 1; function __construct() { $this->y = 2;}}
        $this->atomIs('Ppp')
             ->outIs('PPP')
             ->atomIs('Propertydefinition')
             ->savePropertyAs('propertyname', 'name')
             ->filter(
                $this->side()
                     ->outIs('DEFAULT')
                     ->hasNoIn('RIGHT')
                     ->atomIsNot('Void')
             )
             ->as('results')
             ->goToClass()

             ->outIs('MAGICMETHOD')
             ->atomIs('Magicmethod')
             ->outIs('NAME')
             ->codeIs('__construct')
             ->inIs('NAME')
             ->outIs('BLOCK')
             ->outIs('EXPRESSION')
// Not using atomInside, to avoid values in a condition
//             ->atomInside('Assignation')
             ->atomIs('Assignation')
             ->codeIs('=')
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->is('constant', true)
             ->inIsIE('CODE')
             ->inIs('RIGHT')
             ->outIs('LEFT')
             ->atomIs('Member')
             ->outIs('OBJECT')
             ->atomIs('This')
             ->inIs('OBJECT')
             ->outIs('MEMBER')
             ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)

             // sameParameterAs
             ->back('results');
        $this->prepareQuery();
    }
}

?>
