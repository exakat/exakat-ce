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


namespace Exakat\Analyzer\Functions;

use Exakat\Analyzer\Analyzer;

class WrongNumberOfArguments extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/PropagateCalls',
                     'Complete/MakeClassMethodDefinition',
                     'Complete/CreateMagicProperty',
                     'Complete/SetStringMethodDefinition',
                     'Complete/SetArrayClassDefinition',
                     'Functions/VariableArguments',
                    );
    }

    public function analyze(): void {
        // this is for functions defined within PHP
        $functions = $this->methods->getFunctionsArgsInterval();
        $argsMins = array();
        $argsMaxs = array();

        foreach($functions as $function) {
            $argsMins[makefullnspath($function['name'])] = $function['args_min'];

            if ($function['args_max'] < \MAX_ARGS) {
                $argsMaxs[makefullnspath($function['name'])] = $function['args_max'];
            }
        }

        $stubs = exakat('stubs')->getFunctionsArgsInterval();
        foreach($stubs as $function) {
            $argsMins[makefullnspath($function['name'])] = $function['args_min'];

            if ($function['args_max'] < \MAX_ARGS) {
                $argsMaxs[makefullnspath($function['name'])] = $function['args_max'];
            }
        }

       $this->atomFunctionIs(array_keys($argsMins))
            ->savePropertyAs('fullnspath', 'fnp')
            ->hasNoVariadicArgument()
            ->isLessHash('count', $argsMins, 'fnp')
            ->back('first');
       $this->prepareQuery();

       $this->atomFunctionIs(array_keys($argsMaxs))
            ->savePropertyAs('fullnspath', 'fnp')
            ->hasNoVariadicArgument()
            ->isMoreHash('count', $argsMaxs, 'fnp')
            ->back('first');
       $this->prepareQuery();

        // this is for custom functions
        // function foo(1, 2)
        $this->atomIs(self::FUNCTIONS_CALLS)
             ->outIsIE('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->inIsIE('METHOD') // for methods calls, static or not.
             ->inIsIE('NEW')
             ->inIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('Functions/VariableArguments')
             ->isMore('args_min', 'args_count')
             ->back('first');
        $this->prepareQuery();

        // this is for custom functions
        // foo(...[1,2,])
        $this->atomIs(self::FUNCTIONS_CALLS)
             ->outIsIE('METHOD') // for methods calls, static or not.
             ->outWithRank('ARGUMENT', 0)
             ->is('variadic', true)
             ->savePropertyAs('count', 'args_count')
             ->back('first')
             ->inIsIE('NEW')
             ->inIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('Functions/VariableArguments')
             ->isLess('args_max', 'args_count')
             ->back('first');
        $this->prepareQuery();

        // this is for custom functions
        // foo(...[1,2,])
        $this->atomIs(self::FUNCTIONS_CALLS)
             ->outIsIE('METHOD') // for methods calls, static or not.
             ->outWithRank('ARGUMENT', 0)
             ->is('variadic', true)
             ->savePropertyAs('count', 'args_count')
             ->back('first')
             ->inIsIE('NEW')
             ->inIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('Functions/VariableArguments')
             ->isMore('args_max', 'args_count')
             ->back('first');
        $this->prepareQuery();

        // new A
        // new A()
        // new class() { function __construct($a) {}}
        $this->atomIs('New')
             ->outIs('NEW')
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->outIs('DEFINITION')
             ->atomIs('Magicmethod')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isLess('args_min', 'args_count')
             ->back('first');
        $this->prepareQuery();

        // this is for custom functions
        // new classe(...[1,2,])
        $this->atomIs('New')
             ->outIs('NEW')
             ->outWithRank('ARGUMENT', 0)
             ->is('variadic', true)
             ->savePropertyAs('count', 'args_count')
             ->inIs('ARGUMENT')
             ->outIs('DEFINITION')
             ->atomIs('Magicmethod')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isLess('args_min', 'args_count')
             ->back('first');
        $this->prepareQuery();

        // new A($a)
        // new class($a) { function __construct() {}}
        $this->atomIs('New')
             ->outIs('NEW')
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->outIs('DEFINITION')
             ->atomIs('Magicmethod')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isMore('args_max', 'args_count')
             ->back('first');
        $this->prepareQuery();

        // this is for custom functions
        // new classe(...[1,2,])
        $this->atomIs('New')
             ->outIs('NEW')
             ->outWithRank('ARGUMENT', 0)
             ->is('variadic', true)
             ->savePropertyAs('count', 'args_count')
             ->inIs('ARGUMENT')
             ->outIs('DEFINITION')
             ->atomIs('Magicmethod')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isMore('args_max', 'args_count')
             ->back('first');
        $this->prepareQuery();

        // new self($args)
        $this->atomIs(array('Self', 'Parent'))
             ->hasIn('NEW')
             ->outIsIE('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->inIsIE('METHOD') // for methods calls, static or not.
             ->inIsIE('NEW')
             ->inIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('Functions/VariableArguments')
             ->isMore('args_min', 'args_count')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs(self::FUNCTIONS_CALLS)
             ->outIsIE('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->inIsIE('METHOD') // for methods calls, static or not.
             ->inIsIE('NEW')
             ->inIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('Functions/VariableArguments')
             ->isLess('args_max', 'args_count')
             ->back('first');
        $this->prepareQuery();

        // new self($args)
        $this->atomIs(array('Self', 'Parent'))
             ->hasIn('NEW')
             ->outIsIE('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->inIsIE('METHOD') // for methods calls, static or not.
             ->inIsIE('NEW')
             ->inIs('DEFINITION')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isLess('args_max', 'args_count')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
