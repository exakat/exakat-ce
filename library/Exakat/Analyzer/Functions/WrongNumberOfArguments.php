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
                     'Complete/SetParentDefinition',
                     'Complete/MakeClassMethodDefinition',
                     'Complete/CreateMagicProperty',
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
                     'Complete/IsStubStructure',
                     'Complete/SolveTraitMethods',
                    );
    }

    public function analyze(): void {
        // this is for functions defined within PHP
        $functions = $this->readStubs('getFunctionsArgsInterval');
        $argsMins = array();
        $argsMaxs = array();

        $called = array_flip($this->called->getCalledFunctions());

        foreach ($functions as $function) {
            $name = makeFullNsPath($function['name']);
            if (!isset($called[$name])) {
                continue;
            }
            if ($function['name'] === '\array_keys') {
	            $argsMins[$name] = 1;
            } else {
	            $argsMins[$name] = $function['args_min'];
            }

            if ($function['args_max'] < \MAX_ARGS) {
                $argsMaxs[$name] = $function['args_max'];
            }
        }

        $this->atomFunctionIs(array_keys($argsMins))
             ->analyzerIsNot('self')
             ->savePropertyAs('fullnspath', 'fnp')
             ->hasNoVariadicArgument()
             ->isLessHash('count', $argsMins, 'fnp')
             ->back('first');
        $this->prepareQuery();

        $this->atomFunctionIs(array_keys($argsMaxs))
             ->analyzerIsNot('self')
             ->savePropertyAs('fullnspath', 'fnp')
             ->hasNoVariadicArgument()
             ->isMoreHash('count', $argsMaxs, 'fnp')
             ->back('first');
        $this->prepareQuery();

        // this is for custom functions
        // function foo(1, 2)
        $this->atomIs(self::FUNCTIONS_CALLS)
             ->analyzerIsNot('self')
             ->outIsIE('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->inIsIE('METHOD') // for methods calls, static or not.
             ->inIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('Functions/VariableArguments')
             ->isMore('args_min', 'args_count')
             ->back('first')
             ->inIsIE('NEW')
             ->analyzerIsNot('self');
        $this->prepareQuery();

        // this is for custom functions
        // foo(...[1,2,])
        $this->atomIs(self::FUNCTIONS_CALLS)
             ->analyzerIsNot('self')
             ->outIsIE('METHOD') // for methods calls, static or not.
             ->outWithRank('ARGUMENT', 0)
             ->is('variadic', true)
             ->savePropertyAs('count', 'args_count')
             ->back('first')
             ->inIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('Functions/VariableArguments')
             ->isLess('args_max', 'args_count')
             ->back('first')
             ->inIsIE('NEW')
             ->analyzerIsNot('self');
        $this->prepareQuery();

        // this is for custom functions
        // foo(...[1,2,])
        $this->atomIs(self::FUNCTIONS_CALLS)
             ->analyzerIsNot('self')
             ->outIsIE('METHOD') // for methods calls, static or not.
             ->outWithRank('ARGUMENT', 0)
             ->is('variadic', true)
             ->savePropertyAs('count', 'args_count')
             ->back('first')
             ->inIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_ALL)
             ->analyzerIsNot('Functions/VariableArguments')
             ->isMore('args_max', 'args_count')
             ->back('first')
             ->inIsIE('NEW')
             ->analyzerIsNot('self');
        $this->prepareQuery();

        // new A
        // new A()
        // new class() { function __construct($a) {}}
        $this->atomIs('New')
             ->analyzerIsNot('self')
             ->outIs('NEW')
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->inIs('DEFINITION')
             ->atomIs('Magicmethod')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isLess('args_count', 'args_min')
             ->back('first');
        $this->prepareQuery();

        // this is for custom functions
        // new class() { function __construct($a) {}}
        $this->atomIs('New')
             ->analyzerIsNot('self')
             ->outIs('NEW')
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->inIs('DEFINITION')
             ->atomIs('Magicmethod')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isMore('args_count', 'args_max')
             ->back('first');
        $this->prepareQuery();

        // new A($a)
        // new class($a) { function __construct() {}}
        $this->atomIs('New')
             ->analyzerIsNot('self')
             ->outIs('NEW')
             ->hasNoVariadicArgument()
             ->savePropertyAs('count', 'args_count')
             ->inIs('DEFINITION')
             ->atomIs('Magicmethod')
             ->analyzerIsNot('Functions/VariableArguments')
             ->isMore('args_count', 'args_max')
             ->back('first');
        $this->prepareQuery();

        // new classe(...[1,2,])
        $this->atomIs('New')
             ->analyzerIsNot('self')
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
             ->analyzerIsNot('self')
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
             ->analyzerIsNot('self')
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
             ->analyzerIsNot('self')
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
        $news = $this->readStubs('getNewArgsInterval');
        $argsMins = array();
        $argsMaxs = array();
        foreach ($news as $new) {
            $argsMins[makeFullNsPath($new['class'])] = $new['args_min'];

            if ($new['args_max'] < \MAX_ARGS) {
                $argsMaxs[makeFullNsPath($new['class'])] = $new['args_max'];
            }
        }

        $stubs = $this->readStubs('getConstructorsArgsInterval');
        foreach ($stubs as $stub) {
            list($classe ) = explode('::', $stub['name'], 2);
            $classe = makeFullNsPath($classe);
            $argsMins[$classe] = $stub['args_min'];

            if ($stub['args_max'] < \MAX_ARGS) {
                $argsMaxs[$classe] = $stub['args_max'];
            }
        }

        $this->atomIs('New')
             ->analyzerIsNot('self')
             ->outIs('NEW')
             ->savePropertyAs('fullnspath', 'fnp')
             ->hasNoVariadicArgument()
             ->isLessHash('count', $argsMins, 'fnp')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('New')
             ->analyzerIsNot('self')
             ->outIs('NEW')
             ->savePropertyAs('fullnspath', 'fnp')
             ->hasNoVariadicArgument()
             ->isMoreHash('count', $argsMaxs, 'fnp')
             ->back('first');
        $this->prepareQuery();

        // (new sqlite3())->fetchArray(1,2,3)
        $calledClasses = array_flip($this->called->getCalledClasses());
        $methods = $this->readStubs('getMethodsArgsInterval');
        $argsMins = array();
        $argsMaxs = array();
        foreach ($methods as $method) {
            $class = makeFullNsPath($method['class']);
            if (!isset($calledClasses[$class])) {
                continue;
            }
            $argsMins[$class . '::' . mb_strtolower($method['name'])] = $method['args_min'];

            if ($method['args_max'] < \MAX_ARGS) {
                $argsMaxs[$class . '::' . mb_strtolower($method['name'])] = $method['args_max'];
            }
        }

        $stubs = $this->readStubs('getMethodsArgsInterval');
        foreach ($stubs as $method) {
            $class = makeFullNsPath($method['class']);
            if (!isset($calledClasses[$class])) {
                continue;
            }

            if (!isset($method['args_min'])) {
                continue;
            }
            if (!isset($method['args_max'])) {
                continue;
            }

            $argsMins[$class . '::' . mb_strtolower($method['name'])] = $method['args_min'];

            if ($method['args_max'] < \MAX_ARGS) {
                $argsMaxs[$class . '::' . mb_strtolower($method['name'])] = $method['args_max'];
            }
        }

        // @todo tester avec classes, interfaces et traits
        // @todo tester avec methods statiques
        // @todo : simplify this analysis, as DEFINITION makes most of the work here


        // 'B::C'()
        $this->atomIs('Functioncall')
             ->analyzerIsNot('self')
             ->savePropertyAs('count', 'args_count')
             ->outIs('NAME')
             ->atomIs('String')
             ->inIs('DEFINITION') // might be method, fucntion...
             ->isLess('args_min', 'args_count')
             ->back('first');
        $this->prepareQuery();

        // 'B::C'(1,2,3)
        $this->atomIs('Functioncall')
             ->analyzerIsNot('self')
             ->savePropertyAs('count', 'args_count')
             ->outIs('NAME')
             ->atomIs('String')
             ->inIs('DEFINITION') // might be method, fucntion...
             ->isMore('args_max', 'args_count')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('Methodcall')
             ->analyzerIsNot('self')
             ->outIs('OBJECT')
             ->goToTypehint()
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

			 ->makeMethodFnp('methodfnp', 'fnp', 'name')

             ->isLessHash('count', $argsMins, 'methodfnp')
             ->back('first');
        $this->prepareQuery();

        // Method in a trait
        // For traits, one must go to the definition, and find the trait in the class.
        $this->atomIs('Methodcall')
             ->analyzerIsNot('self')
             ->outIs('OBJECT')
             ->goToTypehint()
             ->inIs('DEFINITION')
             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('USE')
             ->outIs('USE')
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

			 ->makeMethodFnp('methodfnp', 'fnp', 'name')

             ->isLessHash('count', $argsMins, 'methodfnp')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs(self::METHOD_CALLS)
             ->analyzerIsNot('self')
             ->outIs('OBJECT')
             ->goToTypehint()
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

			 ->makeMethodFnp('methodfnp', 'fnp', 'name')

             ->isMoreHash('count', $argsMaxs, 'methodfnp')
             ->back('first');
        $this->prepareQuery();

        // Method in a trait
        // For traits, one must go to the definition, and find the trait in the class.
        $this->atomIs('Methodcall')
             ->analyzerIsNot('self')
             ->outIs('OBJECT')
             ->goToTypehint()
             ->inIs('DEFINITION')
             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('USE')
             ->outIs('USE')
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

			 ->makeMethodFnp('methodfnp', 'fnp', 'name')

             ->isMoreHash('count', $argsMaxs, 'methodfnp')
             ->back('first');
        $this->prepareQuery();

        // Static methods
        $this->atomIs('Staticmethodcall')
             ->analyzerIsNot('self')
             ->outIs('CLASS')
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

			 ->makeMethodFnp('methodfnp', 'fnp', 'name')

             ->isLessHash('count', $argsMins, 'methodfnp')
             ->back('first');
        $this->prepareQuery();

        // The method is defined inside a trait which is in a stub
        $this->atomIs('Staticmethodcall')
             ->analyzerIsNot('self')
             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('USE')
             ->outIs('USE')
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

			 ->makeMethodFnp('methodfnp', 'fnp', 'name')

             ->isLessHash('count', $argsMins, 'methodfnp')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('Staticmethodcall')
             ->analyzerIsNot('self')
             ->outIs('CLASS')
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

			 ->makeMethodFnp('methodfnp', 'fnp', 'name')

             ->isMoreHash('count', $argsMaxs, 'methodfnp')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('Staticmethodcall')
             ->analyzerIsNot('self')
             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->goToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs('USE')
             ->outIs('USE')
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD') // for methods calls, static or not.
             ->hasNoVariadicArgument()

             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'name')
             ->inIs('NAME')

			 ->makeMethodFnp('methodfnp', 'fnp', 'name')

             ->isMoreHash('count', $argsMaxs, 'methodfnp')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
