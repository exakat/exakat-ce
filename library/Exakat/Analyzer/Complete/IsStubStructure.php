<?php declare(strict_types = 1);
/*
 * Copyright 2012-2021 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

class IsStubStructure extends Analyzer {
    protected const PROPERTY = 'isStub';
    protected const PDFF     = 'stubs';

    public function analyze(): void {
        $stubs = exakat(static::PDFF);
        $stubClassConstants   = $stubs->getClassConstantList();
        $stubProperties       = $stubs->getClassPropertyList();
        $stubStaticProperties = $stubs->getClassStaticPropertyList();
        $stubMethods          = $stubs->getClassMethodList();
        $stubStaticMethods    = $stubs->getClassStaticMethodList();

        // Adding FNP for static extended/implemented structures
        $this->atomIs('Staticconstant')
             ->isNot('isStub', true)
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->hasNoIn('DEFINITION')

             ->outIs('CONSTANT')
             ->savePropertyAs('fullcode', 'name')
             ->back('first')

             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->goToAllParents(self::INCLUDE_SELF)

             ->outIs(array('EXTENDS', 'IMPLEMENTS'))
             ->is(static::PROPERTY, true)
             ->savePropertyAs('fullnspath', 'fnp')
             ->raw('filter{ fnp + "::" + name in ***; }', $stubClassConstants)

             ->back('first')
             ->property(static::PROPERTY, true)
             ->raw('sideEffect{ it.get().property("fullnspath", fnp + "::" + name); }');
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
             ->raw('filter{ fnp + "::" + name in ***; }', $stubClassConstants)

             ->back('first')
             ->property(static::PROPERTY, true)
             ->raw('sideEffect{ it.get().property("fullnspath", fnp + "::" + name); }');
        $this->prepareQuery();

        ////////////////////////////////////////////////////////////
        //properties
        // static properties
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
             ->is(static::PROPERTY, true)
             ->savePropertyAs('fullnspath', 'fnp')
             ->raw('filter{ fnp + "::\$" + name in ***; }', $stubProperties)

             ->back('first')
// This is not needed
//             ->raw('sideEffect{ it.get().property("fullnspath", fnp + "::" + name); }')
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
             ->is(static::PROPERTY, true)
             ->savePropertyAs('fullnspath', 'fnp')
             ->raw('filter{ fnp + "::\$" + name in ***; }', $stubProperties)

             ->back('first')
// This is not needed
//             ->raw('sideEffect{ it.get().property("fullnspath", fnp + "::" + name); }')
             ->property(static::PROPERTY, true);
        $this->prepareQuery();

        // avec le typage parameter/return/property
        // methods
        // static methods
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
// This is not needed
//             ->raw('sideEffect{ it.get().property("fullnspath", fnp + "::" + name); }')
             ->property(static::PROPERTY, true);
        $this->prepareQuery();

        //static properties
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
             ->atomIs(array('Variableobject', 'Member', 'Staticproperty', 'Methodcall', 'Functioncall', 'Staticmethodcall')) // @todo remote or not?
             ->goToTypehint() // @todo :  also covers returntypehint ?
             ->is(static::PROPERTY, true)
             ->savePropertyAs('fullnspath', 'fnp')
             ->raw('filter{ fnp + "::" + name.toLowerCase() in ***; }', $stubStaticMethods)

             ->back('first')
// This is not needed
//             ->raw('sideEffect{ it.get().property("fullnspath", fnp + "::" + name); }')
             ->property(static::PROPERTY, true);
        $this->prepareQuery();


    }
}

?>
