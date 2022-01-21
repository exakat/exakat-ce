<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

class CouldBeInt extends CouldBeType {
    public function analyze(): void {
        $intAtoms = array('Integer', 'Addition', 'Multiplication', 'Not', 'Power', 'Preplusplus', 'Postplusplus', 'Spaceship');
        $intFnp = array('\\int');

        // property : based on default value (created or not)
        $this->checkPropertyDefault($intAtoms);

        // property : based usage of the property
        $this->checkPropertyUsage($intAtoms);

        $this->checkPropertyRelayedDefault($intAtoms);

        // property relayed typehint
        $this->checkPropertyRelayedTypehint(array('Scalartypehint'), $intFnp);

        // property relayed typehint
        $this->checkPropertyWithCalls(array('Scalartypehint'), $intFnp);
        $this->checkPropertyWithPHPCalls('int');

        // argument type : $x[$arg]
        $this->atomIs('Propertydefinition')
             ->analyzerIsNot('self')
             ->outIs('DEFINITION')
             ->inIs('INDEX')
             ->atomIs(array('Array'))
             ->back('first');
        $this->prepareQuery();

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
        $this->checkReturnedAtoms($intAtoms);

        $this->checkReturnedCalls(array('Scalartypehint'), $intFnp);

        $this->checkReturnedPHPTypes('int');

        $this->checkReturnedDefault($intAtoms);

        $this->checkReturnedTypehint(array('Scalartypehint'), $intFnp);

        // class a implements b { function () : int {} }
        $this->checkOverwrittenReturnType($intFnp);

        // return type : return $a->b += 3;
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
             ->inIs('INDEX')
             ->atomIs(array('Array'))
             ->back('result');
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

        // class a implements b { function ($a = int) {} }
        $this->checkOverwrittenArgumentType($intFnp);

        // function ($a = int)
        $this->checkArgumentDefaultValues($intAtoms);

        // function ($a) { bar($a);} function bar(array $b) {}
        $this->checkRelayedArgument(array('Scalartypehint'), $intFnp);

        // function ($a) { pow($a, 2);}
        $this->checkRelayedArgumentToPHP('int');

        // argument validation
        $this->checkArgumentValidation(array('\\is_int'), $intAtoms);

        // (int) or intval
        $this->checkCastArgument('T_INT_CAST', array('\\intval'));

        // foo(1); function foo($a) {}
        $this->checkCallingArgumentType(array('Integer'));

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
