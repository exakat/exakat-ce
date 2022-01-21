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
        } elseif (substr($this->source, -5) === '.json') {
            $ini = $this->loadJson($this->source);
        } else {
            return ;
        }

        $ini->functions = array_filter($ini->functions ?? array());
        if (!empty($ini->functions)) {
            $functions = makeFullNsPath($ini->functions);
            $this->atomFunctionIs($functions);
            $this->prepareQuery();
        }

        $ini->constants = array_filter($ini->constants ?? array());
        if (!empty($ini->constants)) {
            $this->atomIs(self::STATIC_NAMES)
                 ->analyzerIs('Constants/ConstantUsage')
                 ->fullnspathIs(makeFullNsPath($ini->constants, \FNP_CONSTANT));
            $this->prepareQuery();
        }

        $ini->classes = array_filter($ini->classes ?? array());
        if (!empty($ini->classes)) {
            $classes = makeFullNsPath($ini->classes);

            $usedClasses = array_intersect(self::getCalledClasses(), $classes);
            if (!empty($usedClasses)) {
                $usedClasses = array_values($usedClasses);
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

        $ini->interfaces = array_filter($ini->interfaces ?? array());
        if (!empty($ini->interfaces)) {
            $interfaces = makeFullNsPath($ini->interfaces);

            $usedInterfaces = array_intersect(self::getCalledinterfaces(), $interfaces);

            if (!empty($usedInterfaces)) {
                $usedInterfaces = array_values($usedInterfaces);
                $this->analyzerIs('Interfaces/InterfaceUsage')
                     ->fullnspathIs($usedInterfaces);
                $this->prepareQuery();
            }
        }

        $ini->enums = array_filter($ini->enums ?? array());
        if (!empty($ini->enums)) {
            $enums = makeFullNsPath($ini->enums);

            $usedEnums = array_intersect(self::getCalledEnums(), $enums);

            if (!empty($usedEnums)) {
                $usedEnums = array_values($usedEnums);
                $this->analyzerIs('Enums/EnumUsage')
                     ->fullnspathIs($usedEnums);
                $this->prepareQuery();
            }
        }

        $ini->traits = array_filter($ini->traits ?? array());
        if (!empty($ini->traits)) {
            $traits = makeFullNsPath($ini->traits);

            $usedTraits = array_intersect(self::getCalledtraits(), $traits);

            if (!empty($usedTraits)) {
                $usedTraits = array_values($usedTraits);
                $this->analyzerIs('Traits/TraitUsage')
                     ->fullnspathIs($usedTraits);
                $this->prepareQuery();
            }
        }

        $ini->namespaces = array_filter($ini->namespaces ?? array());
        if (!empty($ini->namespaces)) {
            $namespaces = makeFullNsPath($ini->namespaces);

            $usedNamespaces = array_intersect($this->getCalledNamespaces(), $namespaces);

            if (!empty($usedNamespaces)) {
                $usedNamespaces = array_values($usedNamespaces);
                $this->analyzerIs('Namespaces/NamespaceUsage')
                     ->fullnspathIs($usedNamespaces);
                $this->prepareQuery();
            }
        }

        $ini->directives = array_filter($ini->directives ?? array());
        if (!empty($ini->directives)) {
            $usedDirectives = array_intersect(self::getCalledDirectives(), $ini->directives);

            if (!empty($usedDirectives)) {
                $usedDirectives = array_values($usedDirectives);
                $this->analyzerIs('Php/DirectivesUsage')
                     ->outWithRank('ARGUMENT', 0)
                     ->noDelimiterIs($usedDirectives, self::CASE_SENSITIVE);
                $this->prepareQuery();
            }
        }

        $ini->classconstants = array_filter($ini->classconstants ?? array());
        if (!empty($ini->classconstants)) {
            $classesconstants = array();
            foreach((array) $ini->classconstants as $c) {
                list($class, $constant) = explode('::', $c);
                array_collect_by($classesconstants, makefullnspath($class), $constant);
            }

            $this->atomIs('Staticconstant')
                 ->outIs('CLASS')
                 ->fullnspathIs(array_keys($classesconstants))
                 ->savePropertyAs('fullnspath', 'fqn')
                 ->back('first')

                 ->outIs('CONSTANT')
                 ->isHash('fullcode', $classesconstants, 'fqn', self::CASE_SENSITIVE)
                 ->back('first');
            $this->prepareQuery();
        }

        // Methods, with typehints (parameters and properties)
        // TODO : Methods with returntypes, local new.
        $ini->methods = array_filter($ini->methods ?? array());
        if (!empty($ini->methods)) {
            $methods = array();
            foreach(array_filter((array) $ini->methods) as $m) {
                list($class, $method) = explode('::', $m);
                array_collect_by($methods, makefullnspath($class), mb_strtolower($method));
            }

            $this->atomIs('Methodcall')
                 ->outIs('OBJECT')
                 // find Typehint for argument or property
                 ->inIs('DEFINITION')
                 ->inIsIE('NAME')
                 ->outIs('TYPEHINT')
                 ->fullnspathIs(array_keys($methods))
                 ->savePropertyAs('fullnspath', 'fqn')
                 ->back('first')

                 ->outIs('METHOD')
                 ->outIs('NAME')
                 ->tokenIs('T_STRING')
                 ->isHash('fullcode', $methods, 'fqn', self::CASE_INSENSITIVE)
                 ->back('first');
            $this->prepareQuery();
        }

        $ini->staticMethods = array_filter($ini->staticMethods ?? array());
        if (!empty($ini->staticMethods)) {
            $methods = array();
            foreach(array_filter((array) $ini->staticMethods) as $m) {
                list($class, $method) = explode('::', $m);
                array_collect_by($methods, makefullnspath($class), mb_strtolower($method));
            }

            $this->atomIs('Staticmethodcall')
                 ->outIs('CLASS')
                 ->fullnspathIs(array_keys($methods))
                 ->savePropertyAs('fullnspath', 'fqn')
                 ->back('first')

                 ->outIs('METHOD')
                 ->outIs('NAME')
                 ->tokenIs('T_STRING')
                 ->isHash('fullcode', $methods, 'fqn', self::CASE_INSENSITIVE)
                 ->back('first');
            $this->prepareQuery();
        }

        // Properties, with typehints (parameters and properties)
        // TODO : Properties with returntypes, local new.
        $ini->properties = array_filter($ini->properties ?? array());
        if (!empty($ini->properties)) {
            $properties = array();
            foreach(array_filter((array) $ini->properties) as $p) {
                list($class, $property) = explode('::', $p);
                array_collect_by($properties, makefullnspath($class), ltrim($property, '$'));
            }

            $this->atomIs('Member')
                 ->outIs('OBJECT')
                 // find Typehint for argument or property
                 ->inIs('DEFINITION')
                 ->inIsIE('NAME')
                 ->outIs('TYPEHINT')
                 ->atomIs(self::STATIC_NAMES)
                 ->fullnspathIs(array_keys($properties))
                 ->savePropertyAs('fullnspath', 'fqn')
                 ->back('first')

                 ->outIs('MEMBER')
                 ->isHash('fullcode', $properties, 'fqn', self::CASE_SENSITIVE)
                 ->back('first');
            $this->prepareQuery();
        }

        $ini->staticProperties = array_filter($ini->staticProperties ?? array());
        if (!empty($ini->staticProperties)) {
            $properties = array();
            foreach(array_filter((array) $ini->properties) as $p) {
                list($class, $property) = explode('::', $p);
                array_collect_by($properties, makefullnspath($class), $property);
            }

            $this->atomIs('Staticproperty')
                 ->outIs('CLASS')
                 ->fullnspathIs(array_keys($properties))
                 ->savePropertyAs('fullnspath', 'fqn')
                 ->back('first')

                 ->outIs('MEMBER')
                 ->isHash('fullcode', $properties, 'fqn', self::CASE_SENSITIVE)
                 ->back('first');
            $this->prepareQuery();
        }
    }
}

?>
