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

use Exakat\Data\Methods;
use Exakat\Analyzer\Analyzer;
use Exakat\Query\DSL\SavePropertyAs;

class VariableTypehint extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                    );
    }

    public function analyze(): void {
        // @todo : run this into a while loop to make sure the data are actually propagated far enough
        // by counting the added types, and stopping when this count is 0
        // $a = 0; $b = $a; $c = $b; ....
        $this->findTypes();
        $this->findTypes();
    }

    private function findTypes(): void {
        // @todo extends to global variables
        // @todo handles multiple typehints when TYPEHINT are extra=true
        // @todo : handle the case of copies from one typed value to the next one.

        $atoms = array('Variabledefinition', 'Propertydefinition');
        // adding integer typehint
        $this->atomIs($atoms)
             ->hasNoTypehint()
             // only one default
             ->filter(
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIsNot('Void')
                      // Skips self transforming variables
                      // removes multiple assignations when they are of the same type
                      ->raw('groupCount().by(__.choose(__.label())
			     .option("Integer",         constant("int"))
//			     .option("Addition",        constant("int"))
			     .option("Multiplication",  constant("int"))
			     .option("Power",           constant("int"))
			     .option("Null",            constant("null"))
			     .option("String",          constant("string"))
			     .option("Heredoc",         constant("string"))
			     .option("Magicconstant",   constant("string"))
			     .option("Concatenation",   constant("string"))
			     .option("Arrayliteral",    constant("array"))
			     .option("Boolean",         constant("bool"))
			     .option("Comparison",      constant("bool"))
			     .option("New",             constant("object"))
			     .option("Float",           constant("float"))
			     ).unfold().count().is(eq(1))')
             )

             ->outIs('DEFAULT')
             //'Addition', removed, to handle array addition also
             ->atomIs(array('Integer', 'Null', 'String', 'Magicconstant', 'Heredoc', 'Arrayliteral', 'Boolean', 'Float', 'Concatenation', 'Multiplication',  'Power'), self::WITH_CONSTANTS)
             ->savePropertyAs('label', 'atomValue')
             ->raw(<<<'GREMLIN'
choose(__.label())
	.option("Integer",        constant("\\int"))
	.option("Addition",       constant("\\int"))
	.option("Multiplication", constant("\\int"))
	.option("Power",          constant("\\int"))

	.option("Null",           constant("\\null"))

	.option("String",         constant("\\string"))
	.option("Heredoc",        constant("\\string"))
	.option("Magicconstant",  constant("\\string"))
	.option("Concatenation",  constant("\\string"))

	.option("Comparison",     constant("\\bool"))
	.option("Boolean",        constant("\\bool"))

	.option("Arrayliteral",   constant("\\array"))

	.option("Float",          constant("\\float"))

.as("fnp")
GREMLIN
             )
             ->addAtom('Scalartypehint', array(
                             'fullcode'   => 'select("fnp")',
                             'fullnspath' => 'select("fnp")',
                             'ws'         => '{"closing":" "}',
                             'rank'       => 0,
                             'line'       => 0,
                             'extra'      => true,
                          ))

  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )
             ->back('first')
             ->setProperty('typehint', 'one')
             ->sideEffect(
                 $this->side()
                      ->outIs('TYPEHINT')
                      ->atomIs('Void')
                      ->dropIn('TYPEHINT')
             );
        $this->prepareQuery();

        // adding cast values (except object)
        $this->atomIs($atoms)
             ->hasNoTypehint()
             // only one default
             ->filter(
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIsNot('Void')
                      ->count()
                      ->isEqual(1)
             )

             ->outIs('DEFAULT')
             ->atomIs(array('Cast'), self::WITH_CONSTANTS)
             ->tokenIsNot(array('T_UNSET_CAST', 'T_OBJECT_CAST'))
             ->as('fnp')
             ->addAtom('Scalartypehint', array(
                             'fullcode'   => 'select("fnp").by("fullnspath")',
                             'fullnspath' => 'select("fnp").by("fullnspath")',
                             'ws'         => '{"closing":" "}',
                             'rank'       => 0,
                             'line'       => 0,
                             'extra'      => true,
                          ))
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )
             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding cast values (except object)
        $this->atomIs($atoms)
             ->hasNoTypehint()
             // only one default
             ->filter(
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIsNot('Void')
                      ->count()
                      ->isEqual(1)
             )

             ->outIs('DEFAULT')
             ->atomIs(array('Cast'), self::WITH_CONSTANTS)
             ->tokenIs('T_OBJECT_CAST')
             ->addAtom('Scalartypehint', array(
                             'fullcode'   => 'Stdclass',
                             'fullnspath' => '\\stdclass',
                             'ws'         => '{"closing":" "}',
                             'rank'       => 0,
                             'line'       => 0,
                             'extra'      => true,
                          ))
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )

             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding returned type from methodcall
        $this->atomIs($atoms)
             ->hasNoTypehint()
             // only one default
             ->filter(
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIsNot('Void')
                      ->count()
                      ->isEqual(1)
             )

             ->outIs('DEFAULT')
             ->atomIs(array('Functioncall', 'Methodcall', 'Staticmethodcall'), self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->outIs('RETURNTYPE')
             ->atomIsNot('Void')
             ->duplicateNode()
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )

             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding returned type from methodcall
        $types = array('string' => '\\string',
                       'int'    => '\\int',
                       'bool'   => '\\bool',
                       'float'  => '\\float',
                       'array'  => '\\array',
                 );
        foreach ($types as $type => $fullnspath) {
            $returntypes    = $this->readStubs('getFunctionsByReturnType', array($type,  Methods::LOOSE));

            $this->atomIs($atoms)
                 ->hasNoTypehint()
                 // only one default
                 ->filter(
                     $this->side()
                          ->outIs('DEFAULT')
                          ->atomIsNot('Void')
                          ->count()
                          ->isEqual(1)
                 )

                 ->outIs('DEFAULT')
                 ->atomIs(array('Functioncall', 'Methodcall', 'Staticmethodcall'), self::WITH_CONSTANTS)
                 ->fullnspathIs($returntypes)
                 ->addAtom('Identifier', array(
                    'ws'         => '{"closing":" "}',
                    'rank'       => 0,
                    'line'       => 0,
                    'extra'      => true,
                 ))
                 ->setProperty('fullcode', $fullnspath)
                 ->setProperty('fullnspath', $fullnspath)
                 ->as('typehint')
  			     ->as('toTypehint')
                 ->addEFrom('TYPEHINT', 'first')
                 ->optional(
                 		$this->side()
                 			 ->back('first')
                 			 ->inIs('PPP')
                 			 ->addETo('TYPEHINT', 'toTypehint')
                 )

                 ->back('first')
                 ->setProperty('typehint', 'one');
            $this->prepareQuery();
        }

        // @todo : handle PDFF's return type

        // adding new x() with class definition or not
        $this->atomIs($atoms)
             ->hasNoTypehint()
             // only one default
             // could be upgraded to multiple identical new
             ->filter(
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIsNot('Void')
                      ->count()
                      ->isEqual(1)
             )

             ->outIs('DEFAULT')
             ->atomIs('New')
             ->filter(
                 $this->side()
                      ->hasIn('RIGHT')
             )
             ->outIs('NEW')
             ->as('theClass')
             ->has('fullnspath')
             ->savePropertyAs(SavePropertyAs::ATOM, 'definition')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
                'extra'      => true,
             ))
             ->setProperty('fullcode', 'definition', 'fullnspath')
             ->setProperty('fullnspath', 'definition', 'fullnspath')
             ->setProperty('isPhp', 'definition', 'isPhp')
             ->setProperty('isExt', 'definition', 'isExt')
             ->setProperty('isStub', 'definition', 'isStub')
             ->as('typehint')
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )

             ->back('theClass')
             // if the definition is available, make a link to it
             ->optional(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->atomIs('Class')
                      ->addETo('DEFINITION', 'typehint')
             )
             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding property typehint from parameter type
        $this->atomIs($atoms)
             ->hasNoTypehint()
             // only one default
             // could be upgraded to multiple identical new
             ->filter(
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIsNot('Void')
                      ->count()
                      ->isEqual(1)
             )

             ->outIs('DEFAULT')
             ->atomIs('Variable', self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->outIs('TYPEHINT')
             ->has('fullnspath')
             ->as('theClass')
             ->savePropertyAs(SavePropertyAs::ATOM, 'definition')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
                'extra'      => true,
             ))
             ->setProperty('fullcode', 'definition', 'fullnspath')
             ->setProperty('fullnspath', 'definition', 'fullnspath')
             ->setProperty('isPhp', 'definition', 'isPhp')
             ->setProperty('isExt', 'definition', 'isExt')
             ->setProperty('isStub', 'definition', 'isStub')
             ->as('typehint')
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )

             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding parameter typehint from property type
        $this->atomIs($atoms)
             ->hasNoTypehint()
             // only one default
             // could be upgraded to multiple identical new
             ->filter(
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIsNot('Void')
                      ->count()
                      ->isEqual(1)
             )

             ->outIs('DEFAULT')
             ->atomIs(array('Member', 'Staticproperty'), self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->outIs('TYPEHINT')
             ->has('fullnspath')
             ->savePropertyAs(SavePropertyAs::ATOM, 'definition')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
                'extra'      => true,
             ))
             ->setProperty('fullcode', 'definition', 'fullnspath')
             ->setProperty('fullnspath', 'definition', 'fullnspath')
             ->setProperty('isPhp', 'definition', 'isPhp')
             ->setProperty('isExt', 'definition', 'isExt')
             ->setProperty('isStub', 'definition', 'isStub')
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )

             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding new stdclass()
        $this->atomIs($atoms)
             ->hasNoTypehint()
             // only one default
             // could be upgraded to multiple identical new
             ->filter(
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIsNot('Void')
                      ->count()
                      ->isEqual(1)
             )

             ->outIs('DEFAULT')
             ->atomIs(self::FUNCTIONS_CALLS, self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             // Method, functions, etc.
             ->outIs('RETURNTYPE')
             ->inIs('DEFINITION')
             ->has('fullnspath')
             ->savePropertyAs(SavePropertyAs::ATOM, 'definition')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
                'extra'      => true,
             ))
             ->setProperty('fullcode', 'definition', 'fullnspath')
             ->setProperty('fullnspath', 'definition', 'fullnspath')
             ->setProperty('isPhp', 'definition', 'isPhp')
             ->setProperty('isExt', 'definition', 'isExt')
             ->setProperty('isStub', 'definition', 'isStub')
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )

             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding type via catch()
        $this->atomIs($atoms)
             ->hasNoTypehint()

             ->outIs('DEFINITION')
             ->inIs('VARIABLE')
             ->atomIs('Catch')
             ->outIs('CLASS') // all of them
             ->savePropertyAs(SavePropertyAs::ATOM, 'definition')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
                'extra'      => true,
             ))
             ->setProperty('fullcode', 'definition', 'fullnspath')
             ->setProperty('fullnspath', 'definition', 'fullnspath')
             ->setProperty('isPhp', 'definition', 'isPhp')
             ->setProperty('isExt', 'definition', 'isExt')
             ->setProperty('isStub', 'definition', 'isStub')
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )

             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding type via clone of local default
        $this->atomIs($atoms)
             ->hasNoTypehint()

             ->outIs('DEFAULT')
             ->atomIs('Clone')
             ->outIs('CLONE')
             ->inIs('DEFINITION')
             ->has('fullnspath')
             ->savePropertyAs(SavePropertyAs::ATOM, 'definition')
             ->as('theClass')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
                'extra'      => true,
             ))
             ->as('typehint')
             ->setProperty('fullcode', $fullnspath)
             ->setProperty('fullnspath', $fullnspath)
             ->addEFrom('TYPEHINT', 'first')
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )

             ->back('typehint')
             ->addEFrom('DEFINITION', 'theClass')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding type via clone via typehint
        $this->atomIs($atoms)
             ->hasNoTypehint()

             ->outIs('DEFAULT')
             ->atomIs('Clone')
             ->outIs('CLONE')
             ->goToTypehint()
             ->inIs('DEFINITION')
             ->has('fullnspath')
             ->savePropertyAs(SavePropertyAs::ATOM, 'definition')
             ->as('theClass')
             ->back('first')
             ->addAtom('Identifier', array(
                'ws'         => '{"closing":" "}',
                'rank'       => 0,
                'line'       => 0,
                'extra'      => true,
             ))
             ->as('typehint')
             ->setProperty('fullcode', 'definition', 'fullnspath')
             ->setProperty('fullnspath', 'definition', 'fullnspath')
             ->addEFrom('TYPEHINT', 'first')
  			 ->as('toTypehint')
             ->addEFrom('TYPEHINT', 'first')
             ->optional(
             		$this->side()
             			 ->back('first')
             			 ->inIs('PPP')
             			 ->addETo('TYPEHINT', 'toTypehint')
             )

             ->back('typehint')
             ->addEFrom('DEFINITION', 'theClass')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->dropVoidType(); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        /*
                // Case of global variables
                $this->atomIs('Virtualglobal')
                     ->hasNoTypehint()
                     ->dedup();
                $this->prepareQuery();
                */
    }
}

?>
