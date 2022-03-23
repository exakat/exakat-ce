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


namespace Exakat\Analyzer\Common;

use Exakat\Analyzer\Analyzer;

class Extension extends Analyzer {
    protected $source = '';

    public function dependsOn(): array {
        return array('Classes/ClassUsage',
                     'Interfaces/InterfaceUsage',
                     'Traits/TraitUsage',
                     'Constants/ConstantUsage',
                     'Namespaces/NamespaceUsage',
                     'Php/DirectivesUsage',
                     'Complete/PropagateCalls',
                     );
    }

    public function analyze(): void {
        if (empty($this->source)) {
            return;
        }

        if (substr($this->source, -4) === '.ini') {
            $ini = (object) $this->loadIni($this->source);

            $ini->constants = array_filter($ini->constants ?? array());
            $constants = makeFullNsPath($ini->constants, \FNP_CONSTANT);

            $ini->functions = array_filter($ini->functions ?? array());
            $functions = makeFullNsPath($ini->functions);

            $ini->classes = array_filter($ini->classes ?? array());
            $classes = makeFullNsPath($ini->classes);

            $ini->interfaces = array_filter($ini->interfaces ?? array());
            $interfaces = makeFullNsPath($ini->interfaces);

            $ini->traits = array_filter($ini->traits ?? array());
            $traits = makeFullNsPath($ini->traits);

            $ini->enums = array_filter($ini->enums ?? array());
            $enums = makeFullNsPath($ini->enums);

            $ini->namespaces = array_filter($ini->namespaces ?? array());
            $namespaces = makeFullNsPath($ini->namespaces);

            $ini->directives = array_filter($ini->directives ?? array());
            $directives = makeFullNsPath($ini->directives);

            $ini->classconstants = array_filter($ini->classconstants ?? array());
            $classConstants = makeFullNsPath($ini->classconstants);

            $ini->properties = array_filter($ini->properties ?? array());
            $properties = makeFullNsPath($ini->properties);

            $ini->staticproperties = array_filter($ini->staticproperties ?? array());
            $staticProperties = makeFullNsPath($ini->staticproperties);

            $ini->methods = array_filter($ini->methods ?? array());
            $methods = makeFullNsPath($ini->methods);

            $ini->staticmethods = array_filter($ini->staticmethods ?? array());
            $staticMethods = makeFullNsPath($ini->staticmethods);

            $directives = array_filter($ini->directives ?? array());

            $enumCases        = array();

        } elseif (substr($this->source, -5) === '.json') {
            $json = $this->loadJson($this->source);

            $json->constants = array_filter($json->constants ?? array());
            $constants = makeFullNsPath($json->constants, \FNP_CONSTANT);

            $json->functions = array_filter($json->functions ?? array());
            $functions = makeFullNsPath($json->functions);

            $json->classes = array_filter($json->classes ?? array());
            $classes = makeFullNsPath($json->classes);

            $json->interfaces = array_filter($json->interfaces ?? array());
            $interfaces = makeFullNsPath($json->interfaces);

            $json->traits = array_filter($json->traits ?? array());
            $traits = makeFullNsPath($json->traits);

            $json->enums = array_filter($json->enums ?? array());
            $enums = makeFullNsPath($json->enums);

            $json->namespaces = array_filter($json->namespaces ?? array());
            $namespaces = makeFullNsPath($json->namespaces);

            $json->directives = array_filter($json->directives ?? array());
            $directives = makeFullNsPath($json->directives);

            $json->classconstants = array_filter($json->classconstants ?? array());
            $classConstants = makeFullNsPath($json->classconstants);

            $json->properties = array_filter($json->properties ?? array());
            $properties = makeFullNsPath($json->properties);

            $json->staticproperties = array_filter($json->staticproperties ?? array());
            $staticProperties = makeFullNsPath($json->staticproperties);

            $json->methods = array_filter($json->methods ?? array());
            $methods = makeFullNsPath($json->methods);

            $json->staticmethods = array_filter($json->staticmethods ?? array());
            $staticMethods = makeFullNsPath($json->staticmethods);

            $json->directives = array_filter($json->directives ?? array());
            $directives = makeFullNsPath($json->directives);

            $enumCases        = array();

        } elseif (substr($this->source, -5) === '.pdff') {
            $pdff = $this->loadPdff($this->source);

            $functions    = $pdff->getFunctionList();
            $constants    = $pdff->getConstantList();
            $classes      = $pdff->getClassList();
            $interfaces   = $pdff->getInterfaceList();
            $traits       = $pdff->getTraitList();
            $enums        = $pdff->getEnumList();
            $namespaces   = $pdff->getEnumList();
            $directives   = array();

            $classConstants   = $pdff->getClassConstantList();
            $properties       = $pdff->getClassPropertyList();
            $staticProperties = $pdff->getClassStaticPropertyList();
            $methods          = $pdff->getClassMethodList();
            $staticMethods    = $pdff->getClassStaticMethodList();
            $enumCases        = $pdff->getEnumCasesList();

        } else {
            return ;
        }


        $this->processFunctions($functions);
        $this->processClasses($classes);
        $this->processConstants($constants);
        $this->processInterfaces($interfaces);
        $this->processTraits($traits);
        $this->processEnums($enums);
        $this->processNamespaces($namespaces);
        $this->processDirectives($directives);

        $this->processClassConstants($classConstants);
        $this->processProperties($properties);
        $this->processStaticProperties($staticProperties);
        $this->processMethods($methods);
        $this->processStaticMethods($staticMethods);
        $this->processEnumCases($enumCases);
    }

    private function processConstants(array $constants): void {
        if (empty($constants)) {
            return;
        }

        $this->atomIs(self::STATIC_NAMES)
             ->analyzerIs('Constants/ConstantUsage')
             ->fullnspathIs($constants);
        $this->prepareQuery();
    }

    private function processFunctions(array $functions): void {
        if (empty($functions)) {
            return;
        }

        $this->atomFunctionIs($functions);
        $this->prepareQuery();
    }

    private function processClasses(array $classes): void {
        if (empty($classes)) {
            return;
        }

        $usedClasses = array_values(array_intersect(self::getCalledClasses(), $classes));
        if (!empty($usedClasses)) {
            $this->atomIs('New')
                 ->outIs('NEW')
                 ->hasNoIn('DEFINITION')
                 ->fullnspathIs($usedClasses);
            $this->prepareQuery();

            $this->atomIs(array('Staticconstant', 'Staticmethodcall', 'Staticproperty'))
                 ->outIs('CLASS')
                 ->hasNoIn('DEFINITION')
                 ->fullnspathIs($usedClasses);
            $this->prepareQuery();

            $this->atomIs(self::FUNCTIONS_ALL)
                 ->outIs('ARGUMENT')
                 ->outIs('TYPEHINT')
                 ->hasNoIn('DEFINITION')
                 ->fullnspathIs($usedClasses);
            $this->prepareQuery();

            $this->atomIs(self::FUNCTIONS_ALL)
                 ->outIs('RETURNTYPE')
                 ->fullnspathIs($usedClasses);
            $this->prepareQuery();

            $this->atomIs('Catch')
                 ->outIs('CLASS')
                 ->hasNoIn('DEFINITION')
                 ->fullnspathIs($usedClasses);
            $this->prepareQuery();

            $this->atomIs('Instanceof')
                 ->outIs('CLASS')
                 ->hasNoIn('DEFINITION')
                 ->fullnspathIs($usedClasses);
            $this->prepareQuery();
        }
    }

    private function processInterfaces(array $interfaces): void {
        if (empty($interfaces)) {
            return;
        }

        $usedInterfaces = array_values(array_intersect(self::getCalledinterfaces(), $interfaces));
        $this->analyzerIs('Interfaces/InterfaceUsage')
             ->fullnspathIs($usedInterfaces);
        $this->prepareQuery();
    }

    private function processEnums(array $enums): void {
        if (empty($enums)) {
            return;
        }

        $this->analyzerIs('Enums/EnumUsage')
             ->fullnspathIs($enums);
        $this->prepareQuery();
    }

    private function processTraits(array $traits): void {
        if (empty($traits)) {
            return;
        }

        $usedTraits = array_values(array_intersect(self::getCalledTraits(), $traits));
        $this->analyzerIs('Traits/TraitUsage')
             ->fullnspathIs($usedTraits);
        $this->prepareQuery();
    }

    private function processNamespaces(array $namespaces): void {
        if (empty($namespaces)) {
            return;
        }

        $usedNamespaces = array_intersect($this->getCalledNamespaces(), $namespaces);
        if (!empty($usedNamespaces)) {
            $usedNamespaces = array_values($usedNamespaces);
            $this->analyzerIs('Namespaces/NamespaceUsage')
                 ->fullnspathIs($usedNamespaces);
            $this->prepareQuery();
        }
    }

    private function processDirectives(array $directives): void {
        if (empty($directives)) {
            return;
        }

        $usedDirectives = array_intersect(self::getCalledDirectives(), $directives);

        if (!empty($usedDirectives)) {
            $usedDirectives = array_values($usedDirectives);

            $this->analyzerIs('Php/DirectivesUsage')
                 ->outWithRank('ARGUMENT', 0)
                 ->noDelimiterIs($usedDirectives, self::CASE_SENSITIVE);
            $this->prepareQuery();
        }
    }

    private function processClassConstants(array $classconstants): void {
        if (empty($classconstants)) {
            return;
        }

        $classesConstantsHash = array();
        foreach((array) $classconstants as $c) {
            list($class, $constant) = explode('::', $c, 2);
            array_collect_by($classesConstantsHash, makefullnspath($class), $constant);
        }

        $this->atomIs('Staticconstant')
             ->outIs('CLASS')
             ->fullnspathIs(array_keys($classesConstantsHash))
             ->savePropertyAs('fullnspath', 'fqn')
             ->back('first')

             ->outIs('CONSTANT')
             ->isHash('fullcode', $classesConstantsHash, 'fqn', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();
    }

    private function processProperties(array $properties): void {
        if (empty($properties)) {
            return;
        }

        // Properties, with typehints (parameters and properties)
        // TODO : Properties with returntypes, local new.

        $propertiesHash = array();
        foreach(array_filter($properties) as $p) {
            list($class, $property) = explode('::', $p, 2);
            array_collect_by($propertiesHash, makefullnspath($class), ltrim($property, '$'));
        }

        $this->atomIs('Member')
             ->outIs('OBJECT')
             // find Typehint for argument or property
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('TYPEHINT')
             ->atomIs(self::STATIC_NAMES)
             ->fullnspathIs(array_keys($propertiesHash))
             ->savePropertyAs('fullnspath', 'fqn')
             ->back('first')

             ->outIs('MEMBER')
             ->isHash('fullcode', $propertiesHash, 'fqn', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();
    }

    private function processStaticProperties(array $staticproperties): void {
        if (empty($staticproperties)) {
            return;
        }

        // Properties, with typehints (parameters and properties)
        // TODO : Properties with returntypes, local new.
        $propertiesHash = array();
        foreach(array_filter($staticproperties) as $p) {
            list($class, $property) = explode('::', $p, 2);
            array_collect_by($propertiesHash, makefullnspath($class), ltrim($property, '$'));
        }

        $this->atomIs('Staticproperty')
             ->outIs('CLASS')
             ->fullnspathIs(array_keys($propertiesHash))
             ->savePropertyAs('fullnspath', 'fqn')
             ->back('first')

             ->outIs('MEMBER')
             ->isHash('fullcode', $propertiesHash, 'fqn', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();
    }

    private function processMethods(array $methods): void {
        if (empty($methods)) {
            return;
        }

        // Methods, with typehints (parameters and properties)
        // TODO : Methods with returntypes, local new.
        $methodsHash = array();
        foreach(array_filter($methods) as $m) {
            list($class, $method) = explode('::', $m, 2);
            array_collect_by($methodsHash, makefullnspath($class), mb_strtolower($method));
        }

        $this->atomIs('Methodcall')
             ->outIs('OBJECT')
             // find Typehint for argument or property
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('TYPEHINT')
             ->fullnspathIs(array_keys($methodsHash))
             ->savePropertyAs('fullnspath', 'fqn')
             ->back('first')

             ->outIs('METHOD')
             ->outIs('NAME')
             ->tokenIs('T_STRING')
             ->isHash('fullcode', $methodsHash, 'fqn', self::CASE_INSENSITIVE)
             ->back('first');
        $this->prepareQuery();
    }

    private function processStaticMethods(array $staticmethods): void {
        if (empty($staticmethods)) {
            return;
        }

        $staticmethodsHash = array();
        foreach(array_filter($staticmethods) as $m) {
            list($class, $method) = explode('::', $m, 2);
            array_collect_by($staticmethodsHash, makefullnspath($class), mb_strtolower($method));
        }

        $this->atomIs('Staticmethodcall')
             ->outIs('CLASS')
             ->fullnspathIs(array_keys($staticmethodsHash))
             ->savePropertyAs('fullnspath', 'fqn')
             ->back('first')

             ->outIs('METHOD')
             ->outIs('NAME')
             ->tokenIs('T_STRING')
             ->isHash('fullcode', $staticmethodsHash, 'fqn', self::CASE_INSENSITIVE)
             ->back('first');
        $this->prepareQuery();
    }

    private function processEnumCases(array $enumcases): void {
        if (empty($enumcases)) {
            return;
        }

        $enumCasesHash = array();
        foreach((array) $enumcases as $c) {
            list($class, $constant) = explode('::', $c, 2);
            array_collect_by($enumCasesHash, makefullnspath($class), $constant);
        }

        $this->atomIs('Staticconstant')
             ->outIs('CLASS')
             ->fullnspathIs(array_keys($enumCasesHash))
             ->savePropertyAs('fullnspath', 'fqn')
             ->back('first')

             ->outIs('CONSTANT')
             ->isHash('fullcode', $enumCasesHash, 'fqn', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
