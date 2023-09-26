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

namespace Exakat\Analyzer\Complete;

use Exakat\Query\DSL\SavePropertyAs;

class PropagateCalls extends Complete {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                    );
    }

    public function analyze(): void {
        $count = 0;
        // No need to run those twice
        $count += $this->processLocalDefinition();

        $count += $this->propagateGlobals();

        $count += $this->propagateTypehint();
        $count += $this->processFluentInterfaces();

        $count += $this->propagateCalls();

        $this->setCount($count);
    }

    private function propagateCalls(int $level = 0): int {
        $total = 0;

        $total += $this->processReturnedType();
        $total += $this->processParenthesis();

        if ($total > 0 && $level < 15) {
            $total += $this->propagateCalls($level + 1);
        }

        return $total;
    }

    private function processLocalDefinition(): int {
        // @todo : add support for traits
        // @todo : add support for traits's alias and insteadof
        // @todo : add support for traits of traits
        //$a = new A; $a->method()
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs(SavePropertyAs::ATOM, 'name')
              ->inIs('METHOD')
              ->outIs('OBJECT')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs(array('Variabledefinition', 'Parametername', 'Propertydefinition', 'Globaldefinition', 'Staticdefinition', 'Virtualproperty'), self::WITHOUT_CONSTANTS)
              ->outIs('DEFAULT')
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToFirstParent('name')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c1 = $this->rawQuery()->toInt();

        //$a = new A; $a->property
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->inIs('MEMBER')
              ->outIs('OBJECT')
              ->inIs('DEFINITION')
              ->atomIs(array('Variabledefinition', 'Parametername', 'Propertydefinition', 'Globaldefinition', 'Staticdefinition', 'Virtualproperty'), self::WITHOUT_CONSTANTS)
              ->outIs('DEFAULT')
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c2 = $this->rawQuery()->toInt();

        //$a = function () {}; $a()
        $this->atomIs(array('Variabledefinition', 'Parametername', 'Propertydefinition', 'Globaldefinition', 'Staticdefinition', 'Virtualproperty'), self::WITHOUT_CONSTANTS)
             ->outIs('DEFAULT')
             ->atomIs(array('Closure', 'Arrowfunction'), self::WITHOUT_CONSTANTS)
             ->hasIn('RIGHT')
             ->hasNoIn('DEFINITION')
             ->as('origin')
             ->back('first')
             ->outIs('DEFINITION')
             ->inIs('NAME')
             ->atomIs('Functioncall', self::WITHOUT_CONSTANTS)
             ->as('call')
             ->hasNoLinkYet('DEFINITION', 'origin')
             ->addEFrom('DEFINITION', 'origin')
             ->count();
        $c3 = $this->rawQuery()->toInt();

        return $c1 + $c2 + $c3;
    }

    private function processReturnedType(): int {
        // function foo() : X {}; $a = foo(); $a->b();
        $this->atomIs(self::FUNCTIONS_ALL, self::WITHOUT_CONSTANTS)
              ->hasOut('RETURNTYPE')
              ->outIs('DEFINITION')
              ->atomIs(self::FUNCTIONS_CALLS, self::WITHOUT_CONSTANTS)
              ->inIs('DEFAULT')
              ->atomIs(array('Propertydefinition', 'Variabledefinition', 'Globaldefinition', 'Staticdefinition'), self::WITHOUT_CONSTANTS)
              ->outIs('DEFINITION')
              ->inIs('OBJECT')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->inIs('METHOD')
              ->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->as('method')
              ->back('first')

              ->outIs('RETURNTYPE')
              ->inIs('DEFINITION')
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('METHOD')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'method')
              ->addETo('DEFINITION', 'method')
              ->count();
        $c1 = $this->rawQuery()->toInt();

        // function foo() : X {}; foo()->b();
        $this->atomIs(self::FUNCTIONS_ALL, self::WITHOUT_CONSTANTS)
              ->hasOut('RETURNTYPE')
              ->outIs('DEFINITION')
              ->inIs('OBJECT')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->inIs('METHOD')
              ->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->as('method')
              ->back('first')

              ->outIs('RETURNTYPE')
              ->inIs('DEFINITION')
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('METHOD')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'method')
              ->addETo('DEFINITION', 'method')
              ->count();
        $c1 += $this->rawQuery()->toInt();

        // function foo() : X {};foo()->b();
        $this->atomIs(self::FUNCTIONS_ALL, self::WITHOUT_CONSTANTS)
              ->hasOut('RETURNTYPE')
              ->outIs('DEFINITION')
              ->atomIs(self::FUNCTIONS_CALLS, self::WITHOUT_CONSTANTS)
              ->inIs('OBJECT')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->inIs('METHOD')
              ->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->as('method')
              ->back('first')

              ->outIs('RETURNTYPE')
              ->inIs('DEFINITION')
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('METHOD')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'method')
              ->addETo('DEFINITION', 'method')
              ->count();
        $c2 = $this->rawQuery()->toInt();

        $this->atomIs(self::FUNCTIONS_ALL, self::WITHOUT_CONSTANTS)
             ->hasOut('RETURNTYPE')
             ->outIs('DEFINITION')
             ->atomIs(self::FUNCTIONS_CALLS, self::WITHOUT_CONSTANTS)
             ->inIs('DEFAULT')
             ->atomIs(array('Propertydefinition', 'Variabledefinition', 'Globaldefinition', 'Staticdefinition'), self::WITHOUT_CONSTANTS)
             ->outIs('DEFINITION')
             ->inIs('OBJECT')
             ->hasNoIn('DEFINITION')
             ->as('member')
             ->atomIs('Member', self::WITHOUT_CONSTANTS)
             ->outIs('MEMBER')
             ->savePropertyAs('code', 'name')
             ->back('first')

             ->outIs('RETURNTYPE')
             ->inIs('DEFINITION')
             ->atomIs('Class', self::WITHOUT_CONSTANTS)
             ->goToAllParents(self::INCLUDE_SELF)
             ->outIs('PPP')
             ->outIs('PPP')
             ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
             ->as('origin')
             ->hasNoLinkYet('DEFINITION', 'member')
             ->addETo('DEFINITION', 'member')
             ->count();
        $c3 = $this->rawQuery()->toInt();

        return $c1 + $c2 + $c3;
    }

    private function processParenthesis(): int {
        // (new x)->foo()
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->back('first')
              ->outIs('OBJECT')
              ->atomIs('Parenthesis', self::WITHOUT_CONSTANTS)
              ->outIs('CODE')
              ->optional(
                  $this->side()
                       ->atomIs('Assignation')
                       ->outIs('RIGHT')
              )
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c1 = $this->rawQuery()->toInt();

        // (new x)::foo()
        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('OBJECT')
              ->atomIs('Parenthesis', self::WITHOUT_CONSTANTS)
              ->outIs('CODE')
              ->optional(
                  $this->side()
                       ->atomIs('Assignation')
                       ->outIs('RIGHT')
              )
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c2 = $this->rawQuery()->toInt();

        // (new x)::foo()
        $this->atomIs('Staticmethodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->back('first')
              ->outIs('CLASS')
              ->atomIs('Parenthesis', self::WITHOUT_CONSTANTS)
              ->outIs('CODE')
              ->optional(
                  $this->side()
                       ->atomIs('Assignation')
                       ->outIs('RIGHT')
              )
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c3 = $this->rawQuery()->toInt();

        // (new x)::foo()
        $this->atomIs('Staticproperty', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('CLASS')
              ->atomIs('Parenthesis', self::WITHOUT_CONSTANTS)
              ->outIs('CODE')
              ->optional(
                  $this->side()
                       ->atomIs('Assignation')
                       ->outIs('RIGHT')
              )
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c4 = $this->rawQuery()->toInt();

        // (new x)::FOO
        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->back('first')
              ->outIs('CLASS')
              ->atomIs('Parenthesis', self::WITHOUT_CONSTANTS)
              ->outIs('CODE')
              ->optional(
                  $this->side()
                       ->atomIs('Assignation')
                       ->outIs('RIGHT')
              )
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')  // No check on atoms :
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c5 = $this->rawQuery()->toInt();

        return $c1 + $c2 + $c3 + $c4 + $c5;
    }

    private function propagateGlobals(): int {
        $this->atomIs('Virtualglobal', self::WITHOUT_CONSTANTS)
              ->outIs('DEFINITION')
              ->atomIs('Variabledefinition')
              ->outIs('DEFAULT')
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->as('theClass')

              ->back('first')
              ->outIs('DEFINITION')
              ->atomIs('Variabledefinition')
              ->outIs('DEFINITION')
              ->inIs('OBJECT')
              ->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->as('theMethod')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')

              ->back('theClass')
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('METHOD')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'theMethod')
              ->addETo('DEFINITION', 'theMethod')
              ->count();
        $c1 = $this->rawQuery()->toInt();

        $this->atomIs('Virtualglobal')
              ->outIs('DEFINITION')
              ->atomIs('Variabledefinition')
              ->outIs('DEFAULT')
              ->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->inIs('DEFINITION')
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              ->as('theClass')

              ->back('first')
              ->outIs('DEFINITION')
              ->atomIs('Variabledefinition')
              ->outIs('DEFINITION')
              ->inIs('OBJECT')
              ->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->as('theProperty')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')

              ->back('theClass')
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'theProperty')
              ->addETo('DEFINITION', 'theProperty')
              ->count();
        $c2 = $this->rawQuery()->toInt();

        return $c1 + $c2;
    }

    private function propagateTypehint(): int {
        // class a { function m (); }
        // function foo( A $a) { $a->m(); }
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->inIs('METHOD')
              ->outIs('OBJECT')
              ->inIs('DEFINITION')
              ->inIs('NAME')
              ->atomIs('Parameter', self::WITHOUT_CONSTANTS)
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->optional(
                  $this->side()
                       ->atomIs('Interface')
                       ->outIs('DEFINITION')
                       ->atomIs(self::STATIC_NAMES, self::WITHOUT_CONSTANTS)
                       ->inIs('IMPLEMENTS')
              )
              // No check on Atom == Class, as it may not exists
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('METHOD')
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c1 = $this->rawQuery()->toInt();

        $this->atomIs('Member', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->inIs('MEMBER')
              ->outIs('OBJECT')
              ->atomIs('Variableobject')
              ->inIs('DEFINITION')
              ->inIs('NAME')
              ->atomIs('Parameter', self::WITHOUT_CONSTANTS)
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->optional(
                  $this->side()
                       ->atomIs('Interface')
                       ->outIs('DEFINITION')
                       ->atomIs(self::STATIC_NAMES, self::WITHOUT_CONSTANTS)
                       ->inIs('IMPLEMENTS')
              )
              // No check on Atom == Class, as it may not exists
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->samePropertyAs('propertyname', 'name', self::CASE_SENSITIVE)
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c2 = $this->rawQuery()->toInt();

        $this->atomIs('Staticconstant', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('CONSTANT')
              ->atomIs('Name', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->inIs('CONSTANT')
              ->outIs('CLASS')
              ->inIs('DEFINITION')
              ->inIs('NAME')
              ->atomIs('Parameter', self::WITHOUT_CONSTANTS)
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->optional(
                  $this->side()
                       ->atomIs('Interface')
                       ->outIs('DEFINITION')
                       ->atomIs(self::STATIC_NAMES, self::WITHOUT_CONSTANTS)
                       ->inIs('IMPLEMENTS')
              )
              ->atomIs('Class', self::WITHOUT_CONSTANTS)
              // No check on Atom == Class, as it may not exists
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs('CONST')
              ->outIs('CONST')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_SENSITIVE)
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c3 = $this->rawQuery()->toInt();

        // Create link between static Class method and its definition
        // This works outside a class too, for static.
        $this->atomIs('Staticmethodcall', self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('METHOD')
              ->savePropertyAs('lccode', 'name')
              ->inIs('METHOD')
              ->outIs('CLASS')
              ->atomIs('Variable', self::WITHOUT_CONSTANTS)
              ->inIs('DEFINITION')
              ->inIs('NAME')
              ->outIs('TYPEHINT')
              ->inIs('DEFINITION')
              ->optional(
                  $this->side()
                       ->atomIs('Interface')
                       ->outIs('DEFINITION')
                       ->atomIs(self::STATIC_NAMES, self::WITHOUT_CONSTANTS)
                       ->inIs('IMPLEMENTS')
              )
              // No check on Atom == Class, as it may not exists
              ->goToAllParents(self::INCLUDE_SELF)
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->isNot('visibility', 'private')
              ->outIs('NAME')
              ->samePropertyAs('code', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')
              ->addETo('DEFINITION', 'first')
              ->count();
        $c4 = $this->rawQuery()->toInt();

        return $c1 + $c2 + $c3 + $c4;
    }

    private function processFluentInterfaces(): int {
        // @todo : cases for self
        // @todo : case with intersectional and union
        // @todo : what to do without any return type?

        // $a->foo1()->foo2()->foo3() : Using self as return type
        $this->atomIs(array('Methodcall', 'Staticmethodcall'), self::WITHOUT_CONSTANTS)
              ->hasNoIn('DEFINITION')
              ->outIs('METHOD')
              ->atomIs('Methodcallname', self::WITHOUT_CONSTANTS)
              ->savePropertyAs('lccode', 'name')
              ->back('first')

              ->outIs(array('OBJECT', 'CLASS'))
              ->atomIs(array('Methodcall', 'Staticmethodcall'), self::WITHOUT_CONSTANTS)
              ->inIs('DEFINITION')
              ->atomIs(array('Method', 'Magicmethod'))
              ->outIs('RETURNTYPE')
              ->savePropertyAs('fullnspath', 'fnp') // return the same type
              ->inIs(array('METHOD', 'MAGICMETHOD'))

              ->atomIs(self::CLASSES_TRAITS, self::WITHOUT_CONSTANTS)
              ->samePropertyAs('fullnspath', 'fnp')
              ->goToAllParentsTraits(self::INCLUDE_SELF)
              ->outIs(array('METHOD', 'MAGICMETHOD'))
              ->outIs('NAME')
              ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->as('origin')
              ->hasNoLinkYet('DEFINITION', 'first')

              ->addETo('DEFINITION', 'first')
              ->count();
        $res = $this->rawQuery();

        return $res->toInt();
    }
}

?>
