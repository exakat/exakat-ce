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


namespace Exakat\Analyzer\Functions;

use Exakat\Analyzer\Analyzer;

class WrongNumberOfArguments extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/PropagateCalls',
                     'Complete/MakeClassMethodDefinition',
                     'Complete/CreateMagicProperty',
                     'Complete/SetStringMethodDefinition',
                     'Complete/SetArrayClassDefinition',
                     'Complete/FollowClosureDefinition',
                     'Complete/SetClassMethodRemoteDefinition',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                     'Complete/SetClassRemoteDefinitionWithGlobal',
                     'Complete/SetClassRemoteDefinitionWithInjection',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                     'Complete/SetClassRemoteDefinitionWithParenthesis',
                     'Complete/SetClassRemoteDefinitionWithReturnTypehint',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                     'Functions/VariableArguments',
                     'Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // this is for functions defined within PHP
        $functions = $this->methods->getFunctionsArgsInterval();
        $argsMins = array();
        $argsMaxs = array();

        foreach($functions as $function) {
            $argsMins[makeFullNsPath($function['name'])] = $function['args_min'];

            if ($function['args_max'] < \MAX_ARGS) {
                $argsMaxs[makeFullNsPath($function['name'])] = $function['args_max'];
            }
        }

        $stubs = exakat('stubs')->getFunctionsArgsInterval();
        foreach($stubs as $function) {
            $argsMins[makeFullNsPath($function['name'])] = $function['args_min'];

            if ($function['args_max'] < \MAX_ARGS) {
                $argsMaxs[makeFullNsPath($function['name'])] = $function['args_max'];
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
/*        $this->atomIs('New')
             ->outIs('NEW')
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->inIs('DEFINITION')
             ->atomIs('Magicmethod')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isLess('args_min', 'args_count')
             ->back('first');
        $this->prepareQuery();
*/
        // this is for custom functions
        // new class() { function __construct($a) {}}
        $this->atomIs('New')
             ->outIs('NEW')
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->outIs('DEFINITION')
             ->atomIs('Magicmethod')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isLess('args_count', 'args_min')
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
             ->isMore('args_count', 'args_max')
             ->back('first');
        $this->prepareQuery();

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

        // new sqlite3()
        $news = $this->methods->getNewArgsInterval();
        $argsMins = array();
        $argsMaxs = array();
        foreach($news as $new) {
            $argsMins[makeFullNsPath($new['class'])] = $new['args_min'];

            if ($function['args_max'] < \MAX_ARGS) {
                $argsMaxs[makeFullNsPath($new['class'])] = $new['args_max'];
            }
        }

        $stubs = exakat('stubs')->getConstructorsArgsInterval();
        foreach($stubs as $function) {
            list($classe, ) = explode('::', $function['name'], 2);
            $classe = makeFullNsPath($classe);
            $argsMins[$classe] = $function['args_min'];

            if ($function['args_max'] < \MAX_ARGS) {
                $argsMaxs[$classe] = $function['args_max'];
            }
        }

        $this->atomIs('New')
             ->outIs('NEW')
             ->savePropertyAs('fullnspath', 'fnp')
             ->hasNoVariadicArgument()
             ->isLessHash('count', $argsMins, 'fnp')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('New')
             ->outIs('NEW')
             ->savePropertyAs('fullnspath', 'fnp')
             ->hasNoVariadicArgument()
             ->isMoreHash('count', $argsMaxs, 'fnp')
             ->back('first');
        $this->prepareQuery();

        // (new sqlite3())->fetchArray(1,2,3)
        $methods = $this->methods->getMethodsArgsInterval();
        $argsMins = array();
        $argsMaxs = array();
        foreach($methods as $method) {
            $argsMins[makeFullNsPath($method['class']) . '::' . mb_strtolower($method['name'])] = $method['args_min'];

            if ($function['args_max'] < \MAX_ARGS) {
                $argsMaxs[makeFullNsPath($method['class']) . '::' . mb_strtolower($method['name'])] = $method['args_max'];
            }
        }
        $stubs = exakat('stubs')->getMethodsArgsInterval();
        foreach($stubs as $methods) {
            $argsMins[makeFullNsPath($methods['name'])] = $methods['args_min'];

            if ($function['args_max'] < \MAX_ARGS) {
                $argsMaxs[makeFullNsPath($methods['name'])] = $methods['args_max'];
            }
        }
        // tester avec classes, interfaces et traits
        // tester avec methods statiques

        $this->atomIs('Methodcall')
             ->outIs('OBJECT')
             ->goToTypehint()
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

             ->raw('sideEffect{ fnp = fnp + "::" + name.toLowerCase(); }')

             ->isLessHash('count', $argsMins, 'fnp')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs(self::METHOD_CALLS)
             ->outIs('OBJECT')
             ->goToTypehint()
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

             ->raw('sideEffect{ fnp = fnp + "::" + name.toLowerCase(); }')

             ->isMoreHash('count', $argsMaxs, 'fnp')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('Staticmethodcall')
             ->outIs('CLASS')
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

             ->raw('sideEffect{ fnp = fnp + "::" + name.toLowerCase(); }')

             ->isLessHash('count', $argsMins, 'fnp')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('Staticmethodcall')
             ->outIs('CLASS')
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

             ->raw('sideEffect{ fnp = fnp + "::" + name.toLowerCase(); }')

             ->isMoreHash('count', $argsMaxs, 'fnp')
             ->back('first');
        $this->prepareQuery();

    }
}

?>
