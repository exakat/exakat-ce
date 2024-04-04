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

namespace Exakat\Analyzer\Complete;

use Exakat\Analyzer\Analyzer;
use Exakat\Stubs\Stubs;
use const STRICT_COMPARISON;

class IsStubStructure extends Analyzer {
    protected const PROPERTY = 'isStub';
    protected const PDFF     = 'stubs';
    protected Stubs $stubs;

    public function dependsOn(): array {
        return array('Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        $this->stubs = new Stubs(dirname($this->config->ext_root) . '/stubs/',
            $this->config->stubs,
        );
        $this->_analyze();
    }

    protected function _analyze(): void {
        $stubClassConstants   = $this->stubs->getClassConstantList();
        $stubProperties   	  = $this->stubs->getClassPropertyList();
        $stubStaticProperties = $this->stubs->getClassStaticPropertyList();
        $stubMethods   	  	  = $this->stubs->getClassMethodList();
        $stubStaticMethods	  = $this->stubs->getClassStaticMethodList();

        // Adding FNP for static extended/implemented structures
        // A::Const
        $this->atomIs('Staticconstant')
             ->isNot('isStub', true)
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->hasNoIn('DEFINITION')

             ->outIs('CONSTANT')
             ->savePropertyAs('fullcode', 'name')
             ->back('first')

             ->outIs('CLASS')
             ->atomIs(array('Identifier', 'Nsname'))
             ->inIs('DEFINITION')
             ->goToAllParents(self::INCLUDE_SELF)

             ->outIs(array('EXTENDS', 'IMPLEMENTS'))
             ->is(static::PROPERTY, true)
             ->savePropertyAs('fullnspath', 'fnp')
             ->makeConstantFnp('constantFnp', 'fnp', 'name')
             ->sameVariableAs('constantFnp', $stubClassConstants)

             ->back('first')
             ->property(static::PROPERTY, true)
             ->setProperty('fullnspath', 'constantFnp');
        $this->prepareQuery();

        // A $a; $a::Const
        $this->atomIs('Staticconstant')
             ->isNot('isStub', true)
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->hasNoIn('DEFINITION')

             ->outIs('CONSTANT')
             ->savePropertyAs('fullcode', 'name')
             ->back('first')

             ->outIs('CLASS')
             ->atomIs(array('Variable')) // Array?
             ->optional(
                 $this->side()
                      ->goToTypehint()
             )

             ->savePropertyAs('fullnspath', 'fnp')
             ->makeConstantFnp('constantFnp', 'fnp', 'name')
             ->sameVariableAs('constantFnp', $stubClassConstants)

             ->back('first')
             ->property(static::PROPERTY, true)
             ->setProperty('fullnspath', 'constantFnp');
        $this->prepareQuery();

        // Adding FNP for typehinted parameter/properties
        $this->atomIs('Staticconstant')
             ->isNot('isStub', true)
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->hasNoIn('DEFINITION')

             ->outIs('CONSTANT')
             ->savePropertyAs('fullcode', 'name')
             ->back('first')

             ->outIs('CLASS')
             ->atomIs(array('Variable', 'Member', 'Staticproperty', 'Methodcall', 'Functioncall', 'Staticmethodcall')) // @todo remote or not?
             ->goToTypehint() // @todo :  also covers returntypehint ?
             ->is(static::PROPERTY, true)
             ->savePropertyAs('fullnspath', 'fnp')
             ->makeConstantFnp('constantFnp', 'fnp', 'name')
             ->sameVariableAs('constantFnp', $stubClassConstants)

             ->back('first')
             ->property(static::PROPERTY, true)
             ->setProperty('fullnspath', 'constantFnp');
        $this->prepareQuery();

        //		$classConstants = $this->readStubs('getClassConstantList');
        //		print_r($classConstants);die();
        // @todo :  support constants in stubs, like class A { const B; } foo() : A; foo()::B

        ////////////////////////////////////////////////////////////
        //properties (normal)

        $this->atomIs('Member')
             ->isNot('isStub', true)
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->hasNoIn('DEFINITION')

             ->outIs('MEMBER')
             ->tokenIs('T_STRING')
             ->savePropertyAs('fullcode', 'name')
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs(array('Variableobject', 'Member', 'Staticproperty', 'Methodcall', 'Functioncall', 'Staticmethodcall')) // @todo remote or not?
             ->goToTypehint() // @todo :  also covers returntypehint ?

             ->savePropertyAs('fullnspath', 'fnp')
             ->raw('filter{ fnp + "::\$" + name in ***; }', $stubProperties)

             ->back('first')
             ->property(static::PROPERTY, true);
        $this->prepareQuery();

        //static properties
        $this->atomIs('Staticproperty')
             ->isNot('isStub', true)
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->hasNoIn('DEFINITION')

             ->outIs('MEMBER')
             ->tokenIs('T_VARIABLE')
             ->savePropertyAs('fullcode', 'name')
             ->back('first')

             ->outIs('CLASS')
             ->atomIs(array('Variableobject', 'Member', 'Staticproperty', 'Methodcall', 'Functioncall', 'Staticmethodcall')) // @todo remote or not?
             ->goToTypehint() // @todo :  also covers returntypehint ?
             ->savePropertyAs('fullnspath', 'fnp')
             ->raw('filter{ fnp + "::\$" + name in ***; }', $stubStaticProperties)

             ->back('first')
             ->property(static::PROPERTY, true);
        $this->prepareQuery();

        // avec le typage parameter/return/property
        // methods
        $this->atomIs('Methodcall')
             ->isNot('isStub', true)
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->hasNoIn('DEFINITION')

             ->outIs('METHOD')
             ->outIs('NAME')
             ->tokenIs('T_STRING')
             ->savePropertyAs('fullcode', 'name')
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs(array('Variableobject', 'Member', 'Staticproperty', 'Methodcall', 'Functioncall', 'Staticmethodcall')) // @todo remote or not?
             ->goToTypehint() // @todo :  also covers returntypehint ?
             ->is(static::PROPERTY, true)
             ->savePropertyAs('fullnspath', 'fnp')
             ->raw('filter{ fnp + "::" + name.toLowerCase() in ***; }', $stubMethods)

             ->back('first')
             ->property(static::PROPERTY, true);
        $this->prepareQuery();

        // methods defined in a stub trait
        // methods
        // Warning : methods may also be renamed with use exprssion.
        $this->atomIs(self::CLASSES_ALL)
             ->outIs('USE')
             ->outIs('USE')
             ->is(static::PROPERTY, true)
             ->values('fullnspath')
             ->unique();
        $list = $this->rawquery()->toArray();

        $traitsMethods = array_values(array_filter($stubMethods, function (string $a) use ($list): bool {
            list($a ) = explode('::', $a, 2);
            return in_array($a, $list, STRICT_COMPARISON);
        }));
        if (!empty($traitsMethods)) {
            $this->atomIs('Methodcall')
                 ->isNot('isStub', true)
                 ->isNot('isPhp', true)
                 ->isNot('isExt', true)
                 ->hasNoIn('DEFINITION')

                 ->outIs('METHOD')
                 ->outIs('NAME')
                 ->tokenIs('T_STRING')
                 ->savePropertyAs('fullcode', 'name')
                 ->back('first')

                 ->outIs('OBJECT')
                 ->atomIs(array('Variableobject', 'Member', 'Staticproperty', 'Methodcall', 'Functioncall', 'Staticmethodcall')) // @todo remote or not?
                 ->goToTypehint() // @todo :  also covers returntypehint ?
                 ->inIs('DEFINITION')
                 ->goToAllParentsTraits(self::INCLUDE_SELF)
                 ->isNot(static::PROPERTY, true)
                 ->outIs('USE')
                 ->outIs('USE')
                 ->is(static::PROPERTY, true)
                 ->savePropertyAs('fullnspath', 'fnp')
                 ->raw('filter{ fnp + "::" + name.toLowerCase() in ***; }', $traitsMethods)

                 ->back('first')
                 ->property(static::PROPERTY, true);
            $this->prepareQuery();
        }
        // @todo : methods defined in an abstract stub class

        //static methodcall
        $traitsStaticMethods = array_values(array_filter($stubStaticMethods, function (string $a) use ($list): bool {
            list($a ) = explode('::', $a, 2);
            return in_array($a, $list, STRICT_COMPARISON);
        }));
        if (!empty($traitsStaticMethods)) {
            $this->atomIs('Staticmethodcall')
                ->isNot('isStub', true)
                ->isNot('isPhp', true)
                ->isNot('isExt', true)
                ->hasNoIn('DEFINITION')

                ->outIs('METHOD')
                ->outIs('NAME')
                ->tokenIs('T_STRING')
                ->savePropertyAs('fullcode', 'name')
                ->back('first')

                ->outIs('CLASS')
                ->inIs('DEFINITION')
                ->goToAllParentsTraits(self::INCLUDE_SELF)
                ->isNot(static::PROPERTY, true)
                ->outIs('USE')
                ->outIs('USE')
                ->is(static::PROPERTY, true)
                ->savePropertyAs('fullnspath', 'fnp')
                ->raw('filter{ fnp + "::" + name.toLowerCase() in ***; }', $traitsStaticMethods)

                ->back('first')
                ->property(static::PROPERTY, true);
            $this->prepareQuery();
        }

        //normal methodcall
        if (!empty($stubMethods)) {
            $this->atomIs('Methodcall')
                ->isNot('isStub', true)
                ->isNot('isPhp', true)
                ->isNot('isExt', true)
                ->hasNoIn('DEFINITION')

                ->outIs('METHOD')
                ->outIs('NAME')
                ->tokenIs('T_STRING')
                ->savePropertyAs('fullcode', 'name')
                ->back('first')

                ->outIs('OBJECT')
                ->goToTypehint()  // @todo : cas des types multiples? OR, AND, DNF...
                ->savePropertyAs('fullnspath', 'fnp')
                ->raw('filter{ fnp + "::" + name.toLowerCase() in ***; }', $stubMethods)

                ->back('first')
                ->property(static::PROPERTY, true);
            $this->prepareQuery();
        }

        //normal property
        if (!empty($stubProperties)) {
            $this->atomIs('Member')
                ->isNot('isStub', true)
                ->isNot('isPhp', true)
                ->isNot('isExt', true)
                ->hasNoIn('DEFINITION')

                ->outIs('MEMBER')
                ->tokenIs('T_STRING')
                ->savePropertyAs('fullcode', 'name')
                ->back('first')

                ->outIs('OBJECT')
                ->goToTypehint()  // @todo : cas des types multiples? OR, AND, DNF...
                ->savePropertyAs('fullnspath', 'fnp')
                ->raw('filter{ fnp + "::\\$" + name in ***; }', $stubProperties)

                ->back('first')
                ->property(static::PROPERTY, true);
            $this->prepareQuery();
        }
    }
}

?>
