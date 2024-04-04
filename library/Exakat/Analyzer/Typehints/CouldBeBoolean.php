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

namespace Exakat\Analyzer\Typehints;


class CouldBeBoolean extends CouldBeType {
    public function analyze(): void {
        $booleanAtoms = array('Comparison', 'Logical', 'Boolean', 'Not');
        $boolFnp = array('\\bool');

        // property : based on default value (created or not)
        $this->checkPropertyDefault($booleanAtoms, array('T_SPACESHIP'));

        $this->checkPropertyRelayedDefault($booleanAtoms);

        // property relayed typehint
        $this->checkPropertyRelayedTypehint(array('Scalartypehint'), $boolFnp);

        // property relayed typehint
        $this->checkPropertyWithCalls(array('Scalartypehint'), $boolFnp);
        $this->checkPropertyWithPHPCalls('bool');

        // return type
        $this->checkReturnedAtoms($booleanAtoms, array('T_SPACESHIP'));

        $this->checkReturnedCalls(array('Scalartypehint'), $boolFnp);

        $this->checkReturnedPHPTypes('bool');

        $this->checkReturnedDefault($booleanAtoms);

        $this->checkReturnedTypehint(array('Scalartypehint'), $boolFnp);

        // argument type
        $this->checkArgumentUsage(array('Variablearray'));

        // class a implements b { function () : bool {} }
        $this->checkOverwrittenReturnType($boolFnp);

        // argument type
        // class a implements b { function ($a = bool) {} }
        $this->checkOverwrittenArgumentType($boolFnp);

        // function ($a = array())
        $this->checkArgumentDefaultValues($booleanAtoms);

        // function ($a) { bar($a);} function bar(array $b) {}
        $this->checkRelayedArgument(array('Scalartypehint'), $boolFnp);

        // function ($a) { array_diff($a);}
        $this->checkRelayedArgumentToPHP('bool');

        // argument validation
        $this->checkArgumentValidation(array('\\is_bool'), $booleanAtoms);

        // class constant type
        $this->checkConstantType(array('Boolean'));

        // argument because used in a specific operation
        // $arg && ''
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('ARGUMENT')
             ->as('result')
             ->analyzerIsNot('self')
             ->outIs('NAME')
             ->outIs('DEFINITION')
             ->inIs(array('LEFT', 'RIGHT'))
             ->atomIs(array('Logical', 'Not'))
             ->back('result');
        $this->prepareQuery();

        // May also cover if( $arg).,
        // May also cover coalesce, ternary.
        // short assignations
    }
}

?>
