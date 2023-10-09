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

use Exakat\Analyzer\Analyzer;
use Exakat\Data\Methods;
use Exakat\Query\DSL\FollowParAs;

abstract class CouldBeType extends Analyzer {
    final public function dependsOn(): array {
        return array('Variables/IsLocalConstant',
                     'Complete/PropagateConstants',
                     'Complete/CreateDefaultValues',
                     'Complete/OverwrittenMethods',
                     'Complete/OverwrittenProperties',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                     'Functions/IsGenerator',
                    );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////
    //  Argument types
    ////////////////////////////////////////////////////////////////////////////////////////////////////
    protected function checkArgumentDefaultValues(array $atoms = array()): void {
        // foo($b = array())
        $this->atomIs(self::FUNCTIONS_ALL)
             ->optional(
                 $this->side()
                      ->is('abstract', true)
                      ->inIs('OVERWRITE')
             )
             ->outIs('ARGUMENT')
             ->filter(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
             )
             ->as('result')
             ->analyzerIsNot('self')
             ->outIs('DEFAULT')
             ->hasNoIn('RIGHT')
             ->atomIs($atoms, self::WITH_CONSTANTS)
             ->back('result');
        $this->prepareQuery();
    }

    protected function checkArgumentUsage(array $atoms = array()): void {
        // foo($b) { $b[] = 1;}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->optional(
                 $this->side()
                      ->is('abstract', true)
                      ->inIs('OVERWRITE')
             )
             ->outIs('ARGUMENT')
             ->filter(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
             )
             ->as('result')
             ->analyzerIsNot('self')
             ->outIs('DEFINITION')
             ->atomIs($atoms, self::WITH_CONSTANTS)
             ->back('result');
        $this->prepareQuery();
    }

    protected function checkRelayedArgument(array $atoms = array(), array $fullnspath = array()): void {
        // foo($b) { bar($b)} ; function bar(array $c) {}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->optional(
                 $this->side()
                      ->is('abstract', true)
                      ->inIs('OVERWRITE')
             )
             ->outIs('ARGUMENT')
             ->isNot('variadic', true)
             ->filter(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
             )
             ->as('result')
             ->analyzerIsNot('self')
             ->outIs('DEFINITION')
             ->goToParameterDefinition()
             ->isNot('variadic', true)
             ->outIs('TYPEHINT')
             ->atomIs($atoms)
             ->fullnspathIs($fullnspath)
             ->back('result');
        $this->prepareQuery();

        // foo(...$b) { bar($b[x])} ; function bar(array $c) {}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->optional(
                 $this->side()
                      ->is('abstract', true)
                      ->inIs('OVERWRITE')
             )
             ->outIs('ARGUMENT')
             ->filter(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
             )
             ->as('result')
             ->analyzerIsNot('self')
             ->outIs('DEFINITION')
             ->inIs('VARIABLE')
             ->atomIs('Array')
             // Being in a functioncall is implied here
             ->goToParameterDefinition()
             ->isNot('variadic', true)
             ->outIs('TYPEHINT')
             ->atomIs($atoms)
             ->fullnspathIs($fullnspath)
             ->back('result');
        $this->prepareQuery();

        // returntype that is an incoming argument
        // function foo ($a) : array { return $a;}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->outIs('RETURNTYPE')
             ->atomIsNot('Void')
             ->fullnspathIs($fullnspath)
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('RETURNED')
             ->hasIn('RETURN')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->atomIs('Parameter')
             ->not(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIsNot('Void')
             );
        $this->prepareQuery();

        // foo(...$b) { bar($b)} ; function bar(...$c) {}
        // In this case, $b must be an array. No choice possible.

        // Missing : when variadic is applied at call time
        // foo(...$b) { bar(...$b)} ; function bar(...$c) {}

        // foo(...$b) { bar($b)} ; function bar(string ...$c) {}
    }

    protected function checkRelayedArgumentToPHP(string $type = ''): void {
        // foo($b) { bar($b)} ; function bar(array $c) {}
        $ini = $this->methods->getFunctionsByArgType($type, Methods::LOOSE);

        if (empty($ini)) {
            return;
        }

        foreach ($ini as $rank => $functions) {
            // foo($arg) { array_map($arg, '') ; }
            $this->atomIs('Parameter')
                 ->analyzerIsNot('self')
                 ->filter(
                     $this->side()
                          ->outIs('TYPEHINT')
                          ->atomIs('Void')
                 )
                 ->outIs('DEFINITION')
                 ->is('rank', (int) $rank)
                 ->inIs('ARGUMENT')
                 ->functioncallIs($functions)
                 ->back('first');
            $this->prepareQuery();
        }
    }

    protected function checkCallingArgumentType(array $types = array()): void {
        if (empty($types)) {
            return ;
        }

        // foo($arg) { array_map($arg, '') ; }
        $this->atomIs('Parameter')
             ->analyzerIsNot('self')
             ->filter(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
             )
             ->goToParameterUsage()
             ->atomIs($types)

             ->back('first');
        $this->prepareQuery();

        // foo(C $arg) { bar($arg); } function bar($c)
        $this->atomIs('Parameter')
             ->analyzerIsNot('self')
             ->filter(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
             )
             ->goToParameterUsage()
             ->inIs('DEFINITION')  // argument and variables
             ->outIs('TYPEHINT')
             ->atomIs($types)

             ->back('first');
        $this->prepareQuery();

        // function foo() : A {}; bar(foo()) function bar($c)
        $this->atomIs('Parameter')
             ->analyzerIsNot('self')
             ->filter(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
             )
             ->goToParameterUsage()
             ->atomIs('Functioncall')
             ->inIs('DEFINITION')  // argument and variables
             ->outIs('RETURNTYPE')
             ->atomIs($types)

             ->back('first');
        $this->prepareQuery();

        // private A $t; bar($this->t) function bar($c)
        $this->atomIs('Parameter')
             ->analyzerIsNot('self')
             ->filter(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
             )
             ->goToParameterUsage()
             ->atomIs('Member')
             ->inIs('DEFINITION')  // argument and variables
             ->inIs('PPP')
             ->outIs('TYPEHINT')
             ->atomIs($types)

             ->back('first');
        $this->prepareQuery();
    }

    protected function checkOverwrittenArgumentType(array $fnp = array()): void {
        if (empty($fnp)) {
            return ;
        }

        // foo($arg) {  }
        $this->atomIs('Parameter')
             ->analyzerIsNot('self')
             ->savePropertyAs('rank', 'ranked')
             ->filter(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
             )
             ->inIs('ARGUMENT')
             ->atomIs('Method')
             ->outIs('OVERWRITE')
             ->outWithRank('ARGUMENT', 'ranked')
             ->outIs('TYPEHINT')
             ->fullnspathIs($fnp)
             ->back('first');
        $this->prepareQuery();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////
    //  Return types
    ////////////////////////////////////////////////////////////////////////////////////////////////////
    protected function checkReturnedAtoms(array $atoms = array(), array $noToken = array()): void {
        // return array(1,2,3)
        $this->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('self')
             ->analyzerIsNot('Functions/IsGenerator')
             ->optional(
                 $this->side()
                      ->is('abstract', true)
                      ->inIs('OVERWRITE')
             )
             ->outIs('RETURNED')
             ->hasIn('RETURN')
             ->followParAs(FollowParAs::FOLLOW_PARAS_ONLY)
             ->atomIs($atoms, self::WITH_CONSTANTS);

        if (!empty($noToken)) {
            $this->tokenIsNot($noToken);
        }

        $this->back('first');
        $this->prepareQuery();
    }

    protected function checkOverwrittenReturnType(array $fnp = array()): void {
        if (empty($fnp)) {
            return ;
        }

        // foo($arg) {  }
        $this->atomIs('Method')
             ->analyzerIsNot('self')
             ->outIs('RETURNTYPE')
             ->atomIs('Void')
             ->back('first')
             ->inIs('OVERWRITE')
             ->outIs('RETURNTYPE')
             ->fullnspathIs($fnp)
             ->back('first');
        $this->prepareQuery();
    }

    protected function checkReturnedTypehint(array $atoms = array(), array $fullnspath = array()): void {
        // function foo (array $a) { return $a;}
        $this->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('self')
             ->analyzerIsNot('Functions/IsGenerator')
             ->optional(
                 $this->side()
                      ->is('abstract', true)
                      ->inIs('OVERWRITE')
             )
             ->outIs('RETURNTYPE')
             ->atomIs('Void')
             ->back('first')

             ->outIs('RETURNED')
             ->hasIn('RETURN')
             ->atomIs('Variable', self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->atomIs('Parameter')
             ->outIs('TYPEHINT')
             ->atomIs($atoms)
             ->fullnspathIs($fullnspath)
             ->back('first');
        $this->prepareQuery();

        // class x { private array $p; function foo () { return $this->p;} }
        $this->atomIs(self::FUNCTIONS_ALL)
             ->optional(
                 $this->side()
                      ->is('abstract', true)
                      ->inIs('OVERWRITE')
             )
             ->analyzerIsNot('self')
             ->outIs('RETURNTYPE')
             ->atomIs('Void')
             ->back('first')

             ->outIs('RETURNED')
             ->atomIs('Member', self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->inIs('PPP')
             ->outIs('TYPEHINT')
             ->atomIs($atoms)
             ->fullnspathIs($fullnspath)
             ->back('first');
        $this->prepareQuery();
    }

    protected function checkReturnedDefault(array $atoms = array()): void {
        // return array(1,2,3)
        $this->atomIs(self::FUNCTIONS_ALL)
             ->optional(
                 $this->side()
                      ->is('abstract', true)
                      ->inIs('OVERWRITE')
             )
             ->analyzerIsNot('self')
             ->analyzerIsNot('Functions/IsGenerator')
             ->outIs('RETURNED')
             ->hasIn('RETURN')
             ->atomIs('Variable', self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->atomIs('Parameter')
             ->outIs('DEFAULT')
             ->atomIs($atoms)
             ->back('first');
        $this->prepareQuery();
    }

    protected function checkReturnedCalls(array $atoms = array(), array $fullnspath = array()): void {
        // return array(1,2,3)
        $this->atomIs(self::FUNCTIONS_ALL)
             ->optional(
                 $this->side()
                      ->is('abstract', true)
                      ->inIs('OVERWRITE')
             )
             ->analyzerIsNot('self')
             ->analyzerIsNot('Functions/IsGenerator')
             ->outIs('RETURNED')
             ->atomIs(self::CALLS, self::WITH_VARIABLES)
             ->inIs('DEFINITION')
             ->outIs('RETURNTYPE')
             ->atomIs($atoms)
             ->fullnspathIs($fullnspath)
             ->back('first');
        $this->prepareQuery();
    }

    protected function checkReturnedPHPTypes(string $type = ''): void {
        // foo($b) { bar($b)} ; function bar(array $c) {}
        $ini = $this->methods->getFunctionsByReturnType($type, Methods::LOOSE);

        if (empty($ini)) {
            return;
        }

        $this->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('Functions/IsGenerator')
             ->outIs('RETURNED')
             ->atomIs('Functioncall', self::WITH_VARIABLES)
             ->fullnspathIs($ini)
             ->back('first');
        $this->prepareQuery();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////
    //  Property types
    ////////////////////////////////////////////////////////////////////////////////////////////////////
    // class x { protected $p = array(); }
    // class x { private $p; function foo() {$this->p = array();  } }
    protected function checkPropertyDefault(array $atoms = array(), array $noToken = array()): void {
        $this->atomIs('Propertydefinition')
             ->analyzerIsNot('self')
             ->outIs('DEFAULT')
             ->atomIs($atoms, self::WITH_CONSTANTS);

        if (!empty($noToken)) {
            $this->tokenIsNot($noToken);
        }

        $this->back('first');
        $this->prepareQuery();
    }

    // class x { protected $p = array(); }
    // class x { private $p; function foo() {$this->p = array();  } }
    protected function checkPropertyUsage(array $atoms = array()): void {
        $this->atomIs('Propertydefinition')
             ->analyzerIsNot('self')
             ->outIs('DEFINITION')
             ->inIs()
             ->atomIs($atoms)
             ->back('first');
        $this->prepareQuery();
    }

    protected function checkPropertyRelayedDefault(array $atoms = array()): void {
        $this->atomIs('Propertydefinition')
             ->analyzerIsNot('self')
             ->outIs('DEFAULT')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->outIs('DEFAULT')
             ->atomIs($atoms, self::WITH_CONSTANTS)
             ->back('first');
        $this->prepareQuery();
    }

    protected function checkPropertyRelayedTypehint(array $atoms = array(), array $fullnspath = array()): void {
        // with arguments
        $this->atomIs('Propertydefinition')
             ->analyzerIsNot('self')
             ->outIs('DEFAULT')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->outIs('TYPEHINT')
             ->atomIs($atoms)
             ->fullnspathIs($fullnspath)
             ->back('first');
        $this->prepareQuery();

        // with return type
        $this->atomIs('Propertydefinition')
             ->analyzerIsNot('self')
             ->outIs('DEFINITION')
             ->inIs('RETURNED')
             ->outIs('RETURNTYPE')
             ->atomIs($atoms)
             ->fullnspathIs($fullnspath)
             ->back('first');
        $this->prepareQuery();
    }

    protected function checkPropertyWithCalls(array $atoms = array(), array $fullnspath = array()): void {
        // class x { private $p; function foo() { $this->p = bar(); }} function bar() : array {}
        $this->atomIs('Propertydefinition')
             ->analyzerIsNot('self')
             ->outIs('DEFAULT')
             ->atomIs(self::CALLS, self::WITH_VARIABLES)
             ->inIs('DEFINITION')
             ->outIs('RETURNTYPE')
             ->atomIs($atoms)
             ->fullnspathIs($fullnspath)
             ->back('first');
        $this->prepareQuery();

        // class x { private $p; function foo() { bar($this->p); }} function bar(array $a) {}
        $this->atomIs('Propertydefinition')
             ->analyzerIsNot('self')
             ->outIs('DEFINITION')
             ->goToParameterDefinition()
             ->outIs('TYPEHINT')
             ->atomIs($atoms)
             ->fullnspathIs($fullnspath)
             ->back('first');
        $this->prepareQuery();
    }

    protected function checkPropertyWithPHPCalls(string $type = ''): void {
        // foo($b) { bar($b)} ; function bar(array $c) {}
        $ini = $this->methods->getFunctionsByReturnType($type, Methods::LOOSE);

        if (!empty($ini)) {
            $this->atomIs('Propertydefinition')
                 ->analyzerIsNot('self')
                 ->outIs('DEFAULT')
                 ->atomIs('Functioncall', self::WITH_VARIABLES)
                 ->fullnspathIs($ini)
                 ->back('first');
            $this->prepareQuery();
        }

        // foo($b) { bar($b)} ; function bar(array $c) {}
        $ini = $this->methods->getFunctionsByArgType($type, Methods::LOOSE);

        foreach ((array) $ini as $position => $functions) {
            $this->atomIs('Propertydefinition')
                 ->analyzerIsNot('self')
                 ->outIs('DEFINITION')
                 ->is('rank', $position)
                 ->inIs('ARGUMENT')
                 ->atomIs('Functioncall', self::WITH_VARIABLES)
                 ->fullnspathIs($functions)
                 ->back('first');
            $this->prepareQuery();
        }
    }

    protected function checkArgumentValidation(array $filters = array(), array $atoms = array()): void {
        // is_array($arg)
        if (!empty($filters)) {
            $this->atomIs(self::FUNCTIONS_ALL)
                 ->optional(
                     $this->side()
                          ->is('abstract', true)
                          ->inIs('OVERWRITE')
                 )
                 ->outIs('ARGUMENT')
                 ->analyzerIsNot('self')
                 ->as('result')
                 ->outIs('DEFINITION')
                 ->inIs('ARGUMENT')
                 ->functioncallIs($filters)
                 ->back('result');
            $this->prepareQuery();
        }

        // comparison
        if (!empty($atoms)) {
            $this->atomIs(self::FUNCTIONS_ALL)
                 ->optional(
                     $this->side()
                          ->is('abstract', true)
                          ->inIs('OVERWRITE')
                 )
                 ->outIs('ARGUMENT')
                 ->analyzerIsNot('self')
                 ->as('result')
                 ->outIs('DEFINITION')
                 ->inIs(array('LEFT', 'RIGHT'))
                 ->atomIs('Comparison')
                 ->inIs(array('LEFT', 'RIGHT'))
                 ->atomIs($atoms, self::WITH_VARIABLES)
                 ->back('result');
            $this->prepareQuery();
        }
    }

    protected function checkCastArgument(string $token = '', array $functions = array()): void {
        // (string) $arg
        if (!empty($token)) {
            $this->atomIs(self::FUNCTIONS_ALL)
                 ->optional(
                     $this->side()
                          ->is('abstract', true)
                          ->inIs('OVERWRITE')
                 )
                 ->optional(
                     $this->side()
                          ->outIs('OVERWRITE')
                 )
                 ->outIs('ARGUMENT')
                 ->analyzerIsNot('self')
                 ->as('result')
                 ->outIs('DEFINITION')
                 ->inIs('CAST')
                 ->atomIs('Cast')
                 ->tokenIs($token)
                 ->back('result');
            $this->prepareQuery();
        }

        // conversion
        if (!empty($functions)) {
            $this->atomIs(self::FUNCTIONS_ALL)
                 ->optional(
                     $this->side()
                          ->is('abstract', true)
                          ->inIs('OVERWRITE')
                 )
                 ->outIs('ARGUMENT')
                 ->analyzerIsNot('self')
                 ->as('result')
                 ->outIs('DEFINITION')
                 ->inIs('ARGUMENT')
                 ->fullnspathIs($functions)
                 ->back('result');
            $this->prepareQuery();
        }
    }
}

?>
