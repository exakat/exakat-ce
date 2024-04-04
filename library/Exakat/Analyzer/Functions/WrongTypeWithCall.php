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

namespace Exakat\Analyzer\Functions;

use Exakat\Analyzer\Analyzer;

class WrongTypeWithCall extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/PropagateConstants',
                     'Complete/FollowClosureDefinition',
                     'Complete/MakeClassMethodDefinition',
                     'Complete/SetClassMethodRemoteDefinition',
                     'Complete/SetClassRemoteDefinitionWithParenthesis',
                     'Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // foo(1); function foo(string $s) {}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->savePropertyAs('rank', 'ranked')
             ->isNot('variadic', true)
             ->outIs('TYPEHINT')
             ->savePropertyAs('fullnspath', 'fnp')
             ->atomIs('Scalartypehint')
             ->back('first')

             ->outIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_CALLS)
             ->analyzerIsNot('self')
             ->as('results')
             ->outIsIE('METHOD')
             ->outWithRank('ARGUMENT', 'ranked')
             ->atomIs(array('Integer',
                            'String',
                            'Arrayliteral',
                            'Concatenation',
                            'Addition',
                            'Power',
                            'Float',
                            'Cast',
                            'Comparison',
                            'Bitshift',
                            ), self::WITH_CONSTANTS)
             ->checkTypeWithAtom('fnp')
             ->back('results');
        $this->prepareQuery();

        // function foo(string $s) { bar($s)}  function bar(int $i) {}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->savePropertyAs('rank', 'ranked')
             ->isNot('variadic', true)
             ->outIs('TYPEHINT')
             ->savePropertyAs('fullnspath', 'fnp')
             ->atomIs('Scalartypehint')
             ->back('first')

             ->outIs('DEFINITION')
             ->outIsIE('NEW')
             ->atomIs(self::FUNCTIONS_CALLS)
             ->analyzerIsNot('self')
             ->as('results')
             ->outIsIE('METHOD')
             ->outWithRank('ARGUMENT', 'ranked')
             ->atomIs(array('Variable',
                            'Member',
                            'Staticproperty',
                            ))
             ->goToTypehint()
             ->atomIs('Scalartypehint')
             ->fullnspathIsNot('\\void')
             ->notSamePropertyAs('fullnspath', 'fnp')
             ->back('results');
        $this->prepareQuery();

        // foo(new d); function foo(string $s) {}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->savePropertyAs('rank', 'ranked')
             ->isNot('variadic', true)
             ->not(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIsNot(array('Scalartypehint', 'Null'))
             )
             ->back('first')

             ->outIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_CALLS)
             ->analyzerIsNot('self')
             ->as('results')
             ->outIsIE('METHOD')
             ->outWithRank('ARGUMENT', 'ranked')
             ->atomIs(array('New',
                            'Clone',
                            ), self::WITH_VARIABLES)
             ->back('results');
        $this->prepareQuery();

        // calling a class-typed argument with a wrong class : include single type, union and intersections
        // foo(new Y); function foo(x $s) {}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->savePropertyAs('rank', 'ranked')
             ->isNot('variadic', true)
             ->not(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIsNot(array('Identifier', 'Nsname', 'Null'))
             )
             ->collectTypehints('typehints' )
             ->savePropertyAs('typehint', 't')
             ->back('first')

             ->outIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_CALLS)
             ->analyzerIsNot('self')
             ->as('results')
             ->outIsIE('METHOD')
             ->outWithRank('ARGUMENT', 'ranked')
             ->atomIs('New', self::WITH_VARIABLES)
             ->outIs('NEW')
             ->inIs('DEFINITION')
             ->isNotClassCompatible('typehints', 't')
             ->back('results');
        $this->prepareQuery();

        // calling a class-typed argument with a scalar
        // foo(1); function foo(x $s) {}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->savePropertyAs('rank', 'ranked')
             ->isNot('variadic', true)
             ->not(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIsNot(array('Identifier', 'Nsname', 'Null'))
             )
             ->back('first')

             ->outIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_CALLS)
             ->analyzerIsNot('self')
             ->as('results')
             ->outIsIE('METHOD')
             ->outWithRank('ARGUMENT', 'ranked')
             ->atomIs(array('Integer',
                            'String',
                            'Arrayliteral',
                            'Concatenation',
                            'Addition',
                            'Power',
                            'Float',
                            ), self::WITH_CONSTANTS)
             ->back('results');
        $this->prepareQuery();

        // @todo calling a class-typed argument with self/static/parent
        // foo(new Z); function foo(self $s) {}

        // @todo calling a class-typed argument with typed element (with typed property)
        // foo(new Z); function foo(self $s) {}
    }
}

?>
