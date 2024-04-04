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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class ObjectReferences extends Analyzer {
    public function analyze(): void {
        $scalars = $this->loadIni('php_scalar_types.ini', 'types');

        // f(stdclass &$x)
        $this->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('self')
             ->outIs('ARGUMENT')
             ->is('reference', true)

             // No scalar is requested
             ->not(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->fullnspathIs($scalars)
             )

            // One of the requested type is an object
             ->outIs('TYPEHINT')
             ->atomIsNot('Void')
             ->atomIs(array('Identifier', 'Nsname'))
             ->back('first');
        $this->prepareQuery();

        // @todo : case of callable, or iterable : are they typed as object only?

        // f(&$x) and $x->y();
        // f(&$x) and $x->y;
        // No assignation with new inside
        $this->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('self')
             ->outIs('ARGUMENT')
             ->is('reference', true)
             ->filter(
                 $this->side()
                     ->outIs('TYPEHINT')
                     ->atomIs('Void')
             )
             ->savePropertyAs('code', 'variable') // Avoid &
             ->not(
                 $this->side()
                      ->filter(
                          $this->side()
                               ->outIs('DEFINITION')
                               ->inIs('LEFT')
                               ->atomIs('Assignation') // any assignation will break the reference
                      )
             )
             ->outIs('DEFINITION')
             ->inIs(array('OBJECT', 'CLASS'))
             ->atomIs(array('Methodcall', 'Member', 'Staticconstant', 'Staticmethodcall', 'Staticproperty', 'Staticclass'))
             ->back('first');
        $this->prepareQuery();

        // foreach($a as &$b) { $b->method;}
        $this->atomIs('Foreach')
             ->outIs('VALUE')
             ->is('reference', true)
             ->savePropertyAs('code', 'variable')
             ->back('first')
             ->not(
                 $this->side()
                     ->outIs('BLOCK')
                     ->atomInsideNoDefinition(array('Methodcall', 'Member'))
                     ->outIs('OBJECT')
                     ->analyzerIsNot('self')
                     ->samePropertyAs('code', 'variable')
                     ->is('isModified', true)
             )
            ->outIs('BLOCK')
            ->atomInsideNoDefinition(array('Methodcall', 'Member'))
            ->outIs('OBJECT')
            ->analyzerIsNot('self')
            ->samePropertyAs('code', 'variable');
        $this->prepareQuery();

        // @todo &function() { return new A; }

        // @todo $x = new object; then &$x;
    }
}

?>
