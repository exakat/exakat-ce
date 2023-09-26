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

use Exakat\Query\DSL\FollowParAs;

class MakeClassMethodDefinition extends Complete {
    public function dependsOn(): array {
        return array('Complete/SetParentDefinition',
                     'Complete/CreateDefaultValues',
                     'Complete/OverwrittenMethods',
                     'Complete/SetCloneLink',
                     'Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // Warning : no support for overwritten methods : ALL methods are linked

        // Create link between static Class method and its definition
        // This works outside a class too, for static.
        $this->atomIs('Staticmethodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->inIs('METHOD')
             ->outIs('CLASS')
             ->atomIs(array('Identifier', 'Nsname', 'Self', 'Static'), self::WITHOUT_CONSTANTS)
             ->inIs('DEFINITION')
             ->atomIs(self::CLASSES_TRAITS, self::WITHOUT_CONSTANTS)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        $this->atomIs('Staticmethodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->inIs('METHOD')
             ->outIs('CLASS')
             ->atomIs('Static', self::WITHOUT_CONSTANTS)
             ->inIs('DEFINITION')
             ->atomIs(self::CLASSES_TRAITS,  self::WITHOUT_CONSTANTS)
             ->goToAllChildren(self::EXCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Create link between Class method and definition
        // This works only for $this
        // First case for the local class
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('OBJECT')
             ->atomIs('This', self::WITHOUT_CONSTANTS)
             ->back('first')
             ->inIs('DEFINITION')
             ->outIs('OVERWRITE')
             ->atomIs(self::FUNCTIONS_METHOD)
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Second case for the local traits
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('OBJECT')
             ->atomIs('This', self::WITHOUT_CONSTANTS)
             ->inIs('OBJECT')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->back('first')
             ->goToClass(self::CLASSES_TRAITS)
             ->outIs('USE')
             ->outIs('USE')
             ->inIs('DEFINITION')
             ->atomIs('Trait')

             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Third case for the parents
        // class only
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('OBJECT')
             ->atomIs('This', self::WITHOUT_CONSTANTS)
             ->inIs('OBJECT')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->back('first')
             ->goToClass(self::CLASSES_TRAITS)
             ->goToAllParents(self::EXCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // class's traits
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('OBJECT')
             ->atomIs('This', self::WITHOUT_CONSTANTS)
             ->inIs('OBJECT')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->back('first')
             ->goToClass(self::CLASSES_TRAITS)
             ->goToAllParents(self::EXCLUDE_SELF)
             ->outIs('USE')
             ->hasNoOut('BLOCK')
             ->outIs('USE')
             ->inIs('DEFINITION')
             ->atomIs('Trait')
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // This will take care of the first step : class - trait - parent. (Above is currently not detailled, any method is linked.)


        // Create link between Class method and definition
        // This works only for $this
        // use a { a as d }
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->hasNoIn('DEFINITION')
             ->outIs('OBJECT')
             ->atomIs('This', self::WITHOUT_CONSTANTS)
             ->inIs('OBJECT')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->back('first')
             ->goToInstruction(self::CLASSES_TRAITS)
             ->goToAllParents(self::INCLUDE_SELF)

             ->outIs('USE')
             ->outIs('BLOCK')
             ->outIs('EXPRESSION')
             ->atomIs('As', self::WITHOUT_CONSTANTS)
             ->outIs('AS')
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->inIs('AS')
             ->outIs('NAME')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'realname')
             ->inIs('METHOD')
             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->atomIs('Trait', self::WITHOUT_CONSTANTS)
             ->GoToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('lccode', 'realname', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // use a,b { a::d insteadof b }
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('OBJECT')
             ->atomIs('This', self::WITHOUT_CONSTANTS)
             ->inIs('OBJECT')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->back('first')
             ->goToInstruction(self::CLASSES_TRAITS)
             ->goToAllParents(self::INCLUDE_SELF)

             ->outIs('USE')
             ->outIs('BLOCK')
             ->outIs('EXPRESSION')
             ->atomIs('Insteadof', self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->outIs('METHOD')
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->inIs('METHOD')
             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->atomIs('Trait', self::WITHOUT_CONSTANTS)
             ->GoToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Create link between Class method and definition
        // This works only for $this
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('OBJECT')
             ->atomIs('This', self::WITHOUT_CONSTANTS)
             ->inIs('OBJECT')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->back('first')
             ->goToInstruction(self::CLASSES_TRAITS)
             ->goToAllParents(self::INCLUDE_SELF)
             ->as('theClass')

             ->outIs('USE')
             ->outIs('BLOCK')
             ->outIs('EXPRESSION')
             ->atomIs('As', self::WITHOUT_CONSTANTS)
             ->outIs('AS')
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->inIs('AS')
             ->outIs('NAME')
             ->savePropertyAs('lccode', 'realname')
             ->back('theClass')

             ->outIs('USE')
             ->outIs('USE')
             ->inIs('DEFINITION')
             ->atomIs('Trait', self::WITHOUT_CONSTANTS)
             ->GoToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('lccode', 'realname', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Create link between Class method and definition
        // class x { function foo() { $this->b(); }}
        // class y extends x { function b() {  }} // class y has no class FOO
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('OBJECT')
             ->atomIs('This', self::WITHOUT_CONSTANTS)
             ->inIs('OBJECT')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->back('first')
             ->goToFunction()
             ->outIs('NAME')
             ->savePropertyAs('lccode', 'methodname')

             ->goToInstruction(self::CLASSES_TRAITS)
             ->goToAllChildren(self::EXCLUDE_SELF)
             ->as('theClass')

             ->not(
                 $this->side()
                      ->outIs(array('METHOD', 'MAGICMETHOD'))
                      ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
                      ->outIs('NAME')
                      ->samePropertyAs('lccode', 'methodname', self::CASE_INSENSITIVE)
             )

             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->not(
                 $this->side()
                      ->outIs('DEFINITION')
                      ->isEqual('first')
             )
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // function foo(X $x) { $x->foo(); }
        $this->atomIs('Methodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->back('first')
             ->outIs('OBJECT')
             ->atomIs('Variableobject')
             ->goToTypehint()
             ->inIs('DEFINITION')
             ->outIs('METHOD')
             ->as('result')
             ->outIs('NAME')
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Create link between Class method and definition
        // This works only for $this
        $this->atomIs('Staticmethodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->inIs('METHOD')
             ->outIs('CLASS')
             ->atomIs(array('Identifier', 'Nsname', 'Self', 'Static'), self::WITHOUT_CONSTANTS)
             ->inIs('DEFINITION')
             ->goToAllParents(self::INCLUDE_SELF)

             ->outIs('USE')
             ->outIs('BLOCK')
             ->outIs('EXPRESSION')
             ->atomIs(array('As', 'Insteadof'), self::WITHOUT_CONSTANTS)
             ->outIs(array('AS', 'INSTEADOF'))
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->inIs(array('AS', 'INSTEADOF'))
             ->outIs('NAME')
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'realname')
             ->inIs('METHOD')
             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->atomIs('Trait', self::WITHOUT_CONSTANTS)
             ->GoToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->samePropertyAs('lccode', 'realname', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Create link between Class method and definition
        // This works only for $this
        $this->atomIs('Staticmethodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->inIs('METHOD')
             ->outIs('CLASS')
             ->atomIs(array('Identifier', 'Nsname', 'Self', 'Static'), self::WITHOUT_CONSTANTS)
             ->inIs('DEFINITION')
             ->goToAllParents(self::INCLUDE_SELF)
             ->as('theClass')

             ->outIs('USE')
             ->outIs('BLOCK')
             ->outIs('EXPRESSION')
             ->atomIs('As', self::WITHOUT_CONSTANTS)
             ->outIs('AS')
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->inIs('AS')
             ->outIs('NAME')
             ->savePropertyAs('lccode', 'realname')
             ->back('theClass')

             ->outIs('USE')
             ->outIs('USE')
             ->inIs('DEFINITION')
             ->atomIs('Trait', self::WITHOUT_CONSTANTS)
             ->GoToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)

             ->outIs('NAME')
             ->samePropertyAs('lccode', 'realname', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Create link between static Class method and its definition
        // This works outside a class too, for static.
        $this->atomIs('Staticmethodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->inIs('METHOD')
             ->outIs('CLASS')
             ->atomIs(array('Identifier', 'Nsname', 'Self', 'Static'), self::WITHOUT_CONSTANTS)
             ->inIs('DEFINITION')
             ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
             ->goToAllParents(self::EXCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->isNot('visibility', 'private')
             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Create link between static Class method and its definition
        $this->atomIs('Staticmethodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->inIs('METHOD')
             ->outIs('CLASS')
             ->atomIs('Parent', self::WITHOUT_CONSTANTS)
             ->inIs('DEFINITION')
             ->GoToAllParentsTraits(self::INCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->isNot('visibility', 'private')
             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();

        // Create link between constructor and new call
        // new x::class;
        $this->atomIs('New', self::WITHOUT_CONSTANTS)
             ->outIs('NEW')
             ->followParAs(FollowParAs::FOLLOW_PARAS_ONLY)
             ->atomIs(array('String', 'Staticclass'), self::WITH_VARIABLES)
             ->hasNoMethodDefinition()
             ->as('results')
             ->outIsIE('CLASS') // for staticclass
             ->has('fullnspath') // exclude variables, members...
             ->savePropertyAs('fullnspath', 'fnp')
             ->inIs('DEFINITION')
             ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
             ->outIs('MAGICMETHOD')
             ->atomIs('Magicmethod')
             ->outIs('NAME')
             ->codeIs('__construct', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'results')
             ->addETo('DEFINITION', 'results')
             ->back('results')
             ->raw('sideEffect{ it.get().property("fullnspath", fnp + "::__construct"); }');
        $this->prepareQuery();

        // new x;
        $this->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->followParAs(FollowParAs::FOLLOW_PARAS_ONLY)
              ->hasNoMethodDefinition()
              ->as('results')
              ->atomIs(array('Newcall', 'Newcallname', 'Self', 'Parent'), self::WITHOUT_CONSTANTS)
              ->has('fullnspath')
              ->inIs('DEFINITION')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->outIs('MAGICMETHOD')
              ->outIs('NAME')
              ->codeIs('__construct', self::TRANSLATE, self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->addETo('DEFINITION', 'results')
              ->back('results')
              ->raw('sideEffect{ it.get().property("fullnspath", it.get().property("fullnspath").value() + "::__construct"); }');
        $this->prepareQuery();

        // const A = '\a'; new ${A}; @todo
        // $x = '\a'; new $x;
        $this->atomIs('New', self::WITHOUT_CONSTANTS)
              ->outIs('NEW')
              ->followParAs(FollowParAs::FOLLOW_PARAS_ONLY)
              ->hasNoMethodDefinition()
              ->as('results')
              ->atomIs(array('String', 'Staticclass'), self::WITH_VARIABLES)
              ->outIsIE('CLASS')
              ->inIs('DEFINITION')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->outIs('MAGICMETHOD')
              ->outIs('NAME')
              ->codeIs('__construct', self::TRANSLATE, self::CASE_INSENSITIVE)
              ->inIs('NAME')
              ->addETo('DEFINITION', 'results');
        // This query doesn't set fullnspath property
        $this->prepareQuery();

        // constructor is in the class, directly
        // Old style constructor
        // @todo : remove this feature with a directive
        $this->atomIs('New', self::WITHOUT_CONSTANTS)
             ->outIs('NEW')
             ->hasNoMethodDefinition()
             ->as('results')
             ->atomIs(array('Newcall', 'Newcallname', 'Self', 'Parent'), self::WITHOUT_CONSTANTS)
             ->has('fullnspath')
             ->inIs('DEFINITION')
             ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
             ->outIs('NAME')
             ->savePropertyAs('lccode', 'name')
             ->inIs('NAME')
             ->outIs('METHOD')
             ->atomIs('Method')
             ->outIs('NAME')
             ->samePropertyAs('lccode', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'results');
        $this->prepareQuery();

        // constructor is in the parents
        $this->atomIs('New', self::WITHOUT_CONSTANTS)
             ->outIs('NEW')
             ->hasNoMethodDefinition()
             ->as('results')
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->isNot('isStub', true)
             ->atomIs(array('Newcall', 'Newcallname', 'Self', 'Parent'), self::WITHOUT_CONSTANTS)
             ->has('fullnspath')
             ->inIs('DEFINITION')
             ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
             ->outIs('EXTENDS')
             ->inIs('DEFINITION')
              /*
              @todo : check out this could be refactored without too many doubles
              ->raw(<<<'GREMLIN'
until( __.out("MAGICMETHOD").hasLabel("Magicmethod").out("NAME").has("fullcode", "__construct")).repeat( __.out("EXTENDS").in("DEFINITION"))
GREMLIN
              )
              */
             ->outIs('MAGICMETHOD')
             ->atomIs('Magicmethod')
             ->outIs('NAME')
             ->codeIs('__construct', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'results')
             ->addETo('DEFINITION', 'results')
              ->back('results')
              ->raw('sideEffect{ it.get().property("fullnspath", it.get().property("fullnspath").value() + "::__construct"); }');
        $this->prepareQuery();

        // Cases of the attributes (simple case, with no inheritence)
        $this->atomIs(self::ATTRIBUTE_ATOMS, self::WITHOUT_CONSTANTS)
             ->outIs('ATTRIBUTE')
             ->as('attribute')
             ->hasNoMethodDefinition()
             ->inIs('DEFINITION')
             ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
             ->outIs('MAGICMETHOD')
             ->codeIs('__construct', self::TRANSLATE, self::CASE_INSENSITIVE)
             ->hasNoLinkYet('DEFINITION', 'attribute')
             ->addETo('DEFINITION', 'attribute')
             ->back('attribute')
             ->raw('sideEffect{ it.get().property("fullnspath", it.get().property("fullnspath").value() + "::__construct"); }');
        $this->prepareQuery();

        /*
                // Create link between __clone and clone
                // parenthesis, typehint, local new,
                $this->atomIs('Clone', self::WITHOUT_CONSTANTS)
                     ->hasNoMethodDefinition()
                     ->outIs('CLONE')
                     ->as('results')
                     ->goToTypehint()
                     /*
                     ->inIs('DEFINITION')
                     ->inIs('NAME')
                     * /
                     ->outIs('TYPEHINT')

                     ->inIs('DEFINITION')
                     ->atomIs(self::CLASSES_TRAITS, self::WITHOUT_CONSTANTS)
                     ->outIs('MAGICMETHOD')
                     ->codeIs('__clone', self::TRANSLATE, self::CASE_INSENSITIVE)
                     ->hasNoLinkYet('DEFINITION', 'first')
                     ->addETo('DEFINITION', 'first')
                      ->back('results')
                      ->raw('sideEffect{ it.get().property("fullnspath", it.get().property("fullnspath").value() + "::__clone"); }');
                $this->prepareQuery();
                */

        // x $a; $a::Constante -> class x { const Constante}
        $this->atomIs('Staticmethodcall', self::WITHOUT_CONSTANTS)
             ->hasNoMethodDefinition()
             ->outIs('METHOD')
             ->savePropertyAs('lccode', 'name')
             ->back('first')
             ->outIs('CLASS')
             ->atomIs(array('Variable', 'Member', 'Staticproperty'))
             ->goToTypehint()
             ->inIs('DEFINITION')
             ->atomIs(array('Class', 'Classanonymous', 'Interface'), self::WITHOUT_CONSTANTS)
             ->goToAllParents(self::INCLUDE_SELF)
             ->outIs(array('METHOD', 'MAGICMETHOD'))
             ->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
             ->isNot('visibility', 'private')
             ->outIs('NAME')
             ->samePropertyAs('code', 'name', self::CASE_INSENSITIVE)
             ->inIs('NAME')
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addETo('DEFINITION', 'first');
        $this->prepareQuery();
    }
}

?>
