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
use Exakat\Query\DSL\SavePropertyAs;
use Exakat\Data\Methods;

class VariableTypehint extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                    );
    }

    public function analyze(): void {
        // @todo : trun this into a while loop to make sure the data are actually propagated far enough
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
             /*
             ->not(
             	$this->side()
             	     ->outIs('DEFINITION')
             	     ->hasIn('GLOBAL')
             )
             */
             // only one default
             ->filter(
                 $this->side()
                      ->outIs('DEFAULT')
                      ->atomIsNot('Void')
                      // Skips self transforming variables
                      // removes multiple assignations when they are of the same type
                      ->raw('groupCount().by(choose(label())
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
             ->back('first')
             ->initVariable('fnp', "''")
             ->raw(<<<'GREMLIN'
        sideEffect{ 
            fnp = "DEFAULT VALUE";
            switch(atomValue) {
                case 'Integer'        : fnp = "\\int";        break;
                case 'Addition'       : fnp = "\\int";        break;
                case 'Multiplication' : fnp = "\\int";        break;
                case 'Power'          : fnp = "\\int";        break;
                case 'Null'           : fnp = "\\null";       break;
                case 'String'         : fnp = "\\string";     break;
                case 'Heredoc'        : fnp = "\\string";     break;
                case 'Magicconstant'  : fnp = "\\string";     break;
                case 'Concatenation'  : fnp = "\\string";     break;
                case 'Arrayliteral'   : fnp = "\\array";      break;
                case 'Boolean'        : fnp = "\\bool";       break;
                case 'Comparison'     : fnp = "\\bool";       break;
                case 'Float'          : fnp = "\\float";        break;
                default : 
                    fnp = "DEFAULT TYPE";break;
            }
        }
GREMLIN
             )
             ->addAtom('Scalartypehint', array(
                             'fullcode'   => 'fnp',
                             'fullnspath' => 'fnp',
                             'ws'         => '{"closing":" "}',
                             'rank'       => 0,
                             'line'       => 0,
                             'extra'      => true,
                          ))

             ->addEFrom('TYPEHINT', 'first')
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
             ->savePropertyAs('fullnspath', 'fnp')
             ->addAtom('Scalartypehint', array(
                             'fullcode'   => 'fnp',
                             'fullnspath' => 'fnp',
                             'ws'         => '{"closing":" "}',
                             'rank'       => 0,
                             'line'       => 0,
                             'extra'      => true,
                          ))
             ->addEFrom('TYPEHINT', 'first')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->raw('sideEffect( __.out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )'); // remove types for PPP and Propertydefinition
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
             ->addEFrom('TYPEHINT', 'first')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->raw('sideEffect( __.out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )'); // remove types for PPP and Propertydefinition
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
             ->addEFrom('TYPEHINT', 'first')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->raw('sideEffect( __.out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )'); // remove types for PPP and Propertydefinition
        $this->prepareQuery();

        // adding returned type from methodcall
        $types = array('string' => '\\\\string',
                  'int'    => '\\\\int',
                  'bool'   => '\\\\bool',
                  'float'  => '\\\\float',
                  'array'  => '\\\\array',
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
                 ->raw(<<<GREMLIN
sideEffect{
    it.get().property("fullcode",   "$fullnspath");
    it.get().property("fullnspath", "$fullnspath");
}
GREMLIN
                 )
              ->as('typehint')
              ->addEFrom('TYPEHINT', 'first')
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
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));

    if (definition.properties("isPhp").any())  { it.get().property("isPhp", true); }
    if (definition.properties("isExt").any())  { it.get().property("isExt", true); }
    if (definition.properties("isStub").any()) { it.get().property("isStub",true); }
}
GREMLIN
             )
             ->as('typehint')
             ->addEFrom('TYPEHINT', 'first')
             ->back('typehint')
             // if the definition is available, make a link to it
             ->raw('sideEffect( select("theClass").in("DEFINITION").hasLabel("Class").addE("DEFINITION").to("typehint"))')
             ->raw('sideEffect( __.select("first").out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )') // remove types for PPP and Propertydefinition
             ->back('first')
             ->setProperty('typehint', 'one');
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
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));

    if (definition.properties("isPhp").any())  { it.get().property("isPhp", true); }
    if (definition.properties("isExt").any())  { it.get().property("isExt", true); }
    if (definition.properties("isStub").any()) { it.get().property("isStub",true); }
}
GREMLIN
             )
             ->as('typehint')
             ->addEFrom('TYPEHINT', 'first')
             ->back('first')
             ->setProperty('typehint', 'one')
             // if the definition is available, make a link to it
             ->raw('sideEffect( select("theClass").in("DEFINITION").hasLabel("Class").addE("DEFINITION").to("typehint"))')
             ->raw('sideEffect( __.out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )'); // remove types for PPP and Propertydefinition
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
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));

    if (definition.properties("isPhp").any())  { it.get().property("isPhp", true); }
    if (definition.properties("isExt").any())  { it.get().property("isExt", true); }
    if (definition.properties("isStub").any()) { it.get().property("isStub",true); }
}
GREMLIN
             )
             ->addEFrom('TYPEHINT', 'first')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->raw('sideEffect( __.out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )'); // remove types for PPP and Propertydefinition
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
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));

    if (definition.properties("isPhp").any())  { it.get().property("isPhp", true); }
    if (definition.properties("isExt").any())  { it.get().property("isExt", true); }
    if (definition.properties("isStub").any()) { it.get().property("isStub",true); }
}
GREMLIN
             )
             ->addEFrom('TYPEHINT', 'first')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->raw('sideEffect( __.out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )'); // remove types for PPP and Propertydefinition
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
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));

    if (definition.properties("isPhp").any())  { it.get().property("isPhp", true); }
    if (definition.properties("isExt").any())  { it.get().property("isExt", true); }
    if (definition.properties("isStub").any()) { it.get().property("isStub",true); }
}
GREMLIN
             )
             ->addEFrom('TYPEHINT', 'first')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->raw('sideEffect( __.out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )'); // remove types for PPP and Propertydefinition
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
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));
}
GREMLIN
             )
             ->addEFrom('TYPEHINT', 'first')
             ->back('typehint')
             ->addEFrom('DEFINITION', 'theClass')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->raw('sideEffect( __.out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )'); // remove types for PPP and Propertydefinition
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
             ->raw(<<<'GREMLIN'
sideEffect{
    it.get().property("fullcode",   definition.value("fullnspath"));
    it.get().property("fullnspath", definition.value("fullnspath"));
}
GREMLIN
             )
             ->addEFrom('TYPEHINT', 'first')
             ->back('typehint')
             ->addEFrom('DEFINITION', 'theClass')
             ->back('first')
             ->setProperty('typehint', 'one')
             ->raw('sideEffect( __.out("TYPEHINT").hasLabel("Void").inE("TYPEHINT").drop() )'); // remove types for PPP and Propertydefinition
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
