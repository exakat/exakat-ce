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

class CouldBeArray extends CouldBeType {
    // dependsOn is in CouldBeType class

    public function analyze(): void {
        $arrayFnp = array('\\array');

        // property with default
        $this->checkPropertyDefault(array('Arrayliteral'));

        // property relayed default
        $this->checkPropertyRelayedDefault(array('Arrayliteral'));

        // property relayed typehint
        $this->checkPropertyRelayedTypehint(array('Scalartypehint'), $arrayFnp);

        // property relayed typehint
        $this->checkPropertyWithCalls(array('Scalartypehint'), $arrayFnp);
        $this->checkPropertyWithPHPCalls('array');

        // return type
        $this->checkReturnedAtoms(array('Arrayliteral'));

        $this->checkReturnedCalls(array('Scalartypehint'), $arrayFnp);

        $this->checkReturnedPHPTypes('array');

        $this->checkReturnedDefault(array('Arrayliteral'));

        $this->checkReturnedTypehint(array('Scalartypehint'), $arrayFnp);

        // class a implements b { function () : array {} }
        $this->checkOverwrittenReturnType($arrayFnp);

        // argument type
        // class a implements b { function ($a = array) {} }
        $this->checkOverwrittenArgumentType($arrayFnp);

        // $arg[]
        $this->checkArgumentUsage(array('Variablearray'));

        // function ($a = array())
        $this->checkArgumentDefaultValues(array('Arrayliteral'));

        // function ($a) { bar($a);} function bar(array $b) {}
        $this->checkRelayedArgument(array('Scalartypehint'), $arrayFnp);

        // function ($a) { array_diff($a);}
        $this->checkRelayedArgumentToPHP('array');

        // is_string
        $this->checkArgumentValidation(array('\\is_array'), array('Arrayliteral'));

        // argument because used in a specific operation with ...
        $this->atomIs(array('Parameter', 'Ppp'))
             ->not(
                $this->side()
                     ->outIs('TYPEHINT')
                     ->atomIs('Scalartypehint')
                     ->fullnspathIs('\\array')
             )
             ->outIs(array('PPP', 'NAME'))
             ->as('results')
             ->outIs('DEFINITION')
             ->hasIn('ARGUMENT') // functioncall or array
             ->is('variadic', true)
             ->back('results');
        $this->prepareQuery();

        $this->atomIs(self::FUNCTIONS_ALL)
             ->not(
                $this->side()
                     ->outIs('RETURNTYPE')
                     ->atomIs('Scalartypehint')
                     ->fullnspathIs('\\array')
             )
             ->outIs('DEFINITION')
             ->hasIn('ARGUMENT') // functioncall or array
             ->is('variadic', true)
             ->back('first');
        $this->prepareQuery();

    }
}

?>
