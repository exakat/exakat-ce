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

namespace Exakat\Analyzer\Variables;

use Exakat\Analyzer\Analyzer;

class InheritedStaticVariable extends Analyzer {
    // PHP version restrictions
    protected string $phpVersion = '8.1-';

    public function analyze(): void {
        // class a { function foo() { static $i;}} class b extends a; b::foo(); a::foo();
        $this->atomIs('Staticdefinition')
             ->goToFunction()
             ->atomIs('Method')
             ->as('result')
             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')
             ->is('static', true)
             ->isNot('visibility', 'private')
             ->goToClass()
             ->outIs('DEFINITION')
             ->inIs('EXTENDS')
             ->atomIs('Class')
             ->not(
                 $this->side()
                      ->filter(
                          $this->side()
                               ->outIs('METHOD')
                               ->outIs('NAME')
                               ->propertyIs('fullcode', 'name', self::CASE_INSENSITIVE)
                      )
             )
             ->back('result');
        $this->prepareQuery();
    }
}

?>
