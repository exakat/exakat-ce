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

namespace Exakat\Analyzer\Typehints;

use Exakat\Query\DSL\FollowParAs;

class CouldBeFloat extends CouldBeType {
    public function analyze(): void {
        $floatAtoms = array('Float', 'Addition', 'Multiplication', 'Not', 'Power');
        $floatFnp = array('\\float');

        // property : based on default value (created or not)
        $this->checkPropertyDefault($floatAtoms);

        // property : based usage of the property
        $this->checkPropertyUsage($floatAtoms);

        $this->checkPropertyRelayedDefault($floatAtoms);

        // property relayed typehint
        $this->checkPropertyRelayedTypehint(array('Scalartypehint'), $floatFnp);

        // property relayed typehint
        $this->checkPropertyWithCalls(array('Scalartypehint'), $floatFnp);
        $this->checkPropertyWithPHPCalls('float');

        // argument type : $x[$arg]
        $this->atomIs('Propertydefinition')
             ->analyzerIsNot('self')
             ->outIs('DEFINITION')
             ->inIs(array('LEFT', 'RIGHT'))
             ->atomIs(array('Assignation'))
             ->codeIs(array('+=', '-=', '*=', '%=', '/=', '**='), self::TRANSLATE, self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // return type
        $this->checkReturnedAtoms($floatAtoms);

        $this->checkReturnedCalls(array('Scalartypehint'), $floatFnp);

        $this->checkReturnedPHPTypes('float');

        $this->checkReturnedDefault($floatAtoms);

        $this->checkReturnedTypehint(array('Scalartypehint'), $floatFnp);

        // class a implements b { function () : float {} }
        $this->checkOverwrittenReturnType($floatFnp);

        // return type : return $a->b += 3.3;
        $this->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('self')
             ->outIs('RETURNED')
             ->followParAs(FollowParAs::FOLLOW_NONE)
             ->analyzerIsNot('self')
             ->atomIs('Assignation')
             ->codeIs(array('+=', '-=', '*=', '**=', '/=', '%=', '<<=', '>>=', '&=', '|=', '^='))
             ->back('first');
        $this->prepareQuery();

        // argument type : $x[$arg]
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->as('result')
             ->analyzerIsNot('self')
             ->outIs('NAME')
             ->outIs('DEFINITION')
             ->inIs(array('LEFT', 'RIGHT'))
             ->atomIs(array('Assignation'))
             ->codeIs(array('+=', '-=', '*=', '%=', '/=', '**='), self::TRANSLATE, self::CASE_SENSITIVE)
             ->back('result');
        $this->prepareQuery();

        // class a implements b { function (int $a) {} }
        $this->checkOverwrittenArgumentType($floatFnp);

        // function ($a = float)
        $this->checkArgumentDefaultValues($floatAtoms);

        // function ($a) { bar($a);} function bar(float $b) {}
        $this->checkRelayedArgument(array('Scalartypehint'), $floatFnp);

        // function ($a) { pow($a, 2);}
        $this->checkRelayedArgumentToPHP('float');

        // argument validation
        $this->checkArgumentValidation(array('\\is_float'), $floatAtoms);

        // foo(1); function foo($a) {}
        $this->checkCallingArgumentType(array('Float'));

        // argument because used in a specific operation
        // $arg && ''
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->as('result')
             ->analyzerIsNot('self')
             ->outIs('NAME')
             ->outIs('DEFINITION')
             ->inIs(array('LEFT', 'RIGHT'))
             ->atomIs(array('Addition', 'Power', 'Multiplication'))
             ->back('result');
        $this->prepareQuery();

        // May also cover if( $arg).,
        // May also cover coalesce, ternary.
        // short assignations
    }
}

?>
