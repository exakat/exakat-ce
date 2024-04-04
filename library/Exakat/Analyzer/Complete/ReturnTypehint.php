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

class ReturnTypehint extends Analyzer {
    protected int $storageType = self::QUERY_NO_ANALYZED;

    public function analyze(): void {
        // @todo Add type based on PDFF

        // specific for self
        $this->atomIs('Method')
             ->filter(
                 $this->side()
                      ->outIs('RETURNTYPE')
                      ->atomIs('Void')
             )
             ->filter(
                 $this->side()
                      ->outIs('RETURNTYPE')
                      ->count()
                      ->isEqual(1)
             )
             ->outIs('RETURNED')
             ->atomIs('This')
             ->inIs('DEFINITION')
             ->atomIs('Class')

             ->as('theClass')
             ->back('first')
             ->removeLink('RETURNTYPE')
             ->addAtom('Scalartypehint', array(
                             'fullcode'   => 'select("theClass").out("NAME").properties("fullcode").value()',
                             'fullnspath' => 'select("theClass").properties("fullnspath").value()',
                             'line'       => -1,
                             'extra'      => true,
                          ))
              ->addEFrom('RETURNTYPE', 'first');
        $this->prepareQuery();

        // specific for CIT
        $this->atomIs(self::FUNCTIONS_ALL)
             ->filter(
                 $this->side()
                      ->outIs('RETURNTYPE')
                      ->atomIs('Void')
             )
             ->filter(
                 $this->side()
                      ->outIs('RETURNTYPE')
                      ->count()
                      ->isEqual(1)
             )
             ->outIs('RETURNED')
             ->atomIs('New')
             ->outIs('NEW')
             ->atomIsNot('Classanonymous')
             ->has('fullnspath')
             ->as('theClass')
             ->removeLink('RETURNTYPE')
             ->addAtom('Identifier', array(
                             'fullcode'   => 'select("theClass").optional(out("NAME")).properties("fullcode").value()',
                             'fullnspath' => 'select("theClass").properties("fullnspath").value()',
                             'line'       => -1,
                             'extra'      => true,
                          ))
              ->addEFrom('RETURNTYPE', 'first');
        $this->prepareQuery();

        $types = array(array(array('Integer'),
                             '\\\\int',
                             'int'),
                       array(array('String'),
                             '\\\\string',
                             'string'),
                       array(array('Boolean'),
                             '\\\\bool',
                             'bool'),
                       array(array('Float'),
                             '\\\\float',
                             'float'),
                       array(array('Null'),
                             '\\\\null',
                             'null'),
                       array(array('Arrayliteral'),
                             '\\\\array',
                             'array'),
        );

        // type with an atom
        $this->atomIs(self::FUNCTIONS_ALL)
                 ->filter(
                     $this->side()
                          ->outIs('RETURNTYPE')
                          ->atomIs('Void')
                 )
                 ->hasOut('RETURNED')
                 ->not(
                     $this->side()
                          ->outIs('RETURNED')
                          ->atomIsNot(array_merge(...array_column($types, 0)))
                 )
                 ->back('first')
                 ->removeLink('RETURNTYPE')
                 ->raw(<<<'GREMLIN'
 out('RETURNED')
.dedup().by(label)
.as("r")
.addV('Scalartypehint')
         .as("b")
         .property("line", -1)
         .property("extra", true)
         .coalesce(
            select("r").hasLabel('Null')        .select("b").property("fullnspath", '\\null')    .property("fullcode", 'null'),
            select("r").hasLabel('Integer')     .select("b").property("fullnspath", '\\int')     .property("fullcode", 'int'),
            select("r").hasLabel('String')      .select("b").property("fullnspath", '\\string')  .property("fullcode", 'string'),
            select("r").hasLabel('Float')       .select("b").property("fullnspath", '\\float')   .property("fullcode", 'float'),
            select("r").hasLabel('Boolean')     .select("b").property("fullnspath", '\\bool')    .property("fullcode", 'bool'),
            select("r").hasLabel('Arrayliteral').select("b").property("fullnspath", '\\array')   .property("fullcode", 'array'),
         )
.sideEffect(
    select("b").addE("RETURNTYPE").from("first")
)
.sideEffect(
    // Link Returntype to Function
    select("first").coalesce(
        select("first").out("RETURN").count().is(eq(1)).select("first").property("typehinttype", "one"),
        select("first").property("typehinttype", "or")
    )
)
GREMLIN
                 );
        $this->prepareQuery();

        // type with a cast
        $this->atomIs(self::FUNCTIONS_ALL)
             ->filter(
                 $this->side()
                      ->outIs('RETURNTYPE')
                      ->atomIs('Void')
             )
             ->filter(
                 $this->side()
                      ->outIs('RETURNED')
                      ->atomIs('Cast')
             )
             ->back('first')
             ->removeLink('RETURNTYPE')
             ->raw(<<<'GREMLIN'
 out('RETURNED').hasLabel('Cast')
.as("r")
.addV('Scalartypehint')
         .as("b")
         .property("line", -1)
         .property("extra", true)
         .coalesce(
            select("r").has('token', 'T_INT_CAST')    .select("b").property("fullnspath", '\\int')     .property("fullcode", 'int'),
            select("r").has('token', 'T_STRING_CAST') .select("b").property("fullnspath", '\\string')  .property("fullcode", 'string'),
            select("r").has('token', 'T_FLOAT_CAST')  .select("b").property("fullnspath", '\\float')   .property("fullcode", 'float'),
            select("r").has('token', 'T_BOOL_CAST')   .select("b").property("fullnspath", '\\bool')    .property("fullcode", 'bool'),
            select("r").has('token', 'T_ARRAY_CAST')  .select("b").property("fullnspath", '\\array')   .property("fullcode", 'array'),
            select("r").has('token', 'T_OBJECT_CAST') .select("b").property("fullnspath", '\\object')  .property("fullcode", 'array')
         )
.sideEffect(
    select("b").addE("RETURNTYPE").from("first")
)
.sideEffect(
    // Link Returntype to Function
    select("first").coalesce(
        select("first").out("RETURN").count().is(eq(1)).select("first").property("typehinttype", "one"),
        select("first").property("typehinttype", "or")
    )
)
GREMLIN
             );
        $this->prepareQuery();

        // type without a return
        $this->atomIs(self::FUNCTIONS_ALL)
             ->filter(
                 $this->side()
                      ->outIs('RETURNTYPE')
                      ->atomIs('Void')
             )
             ->not(
                 $this->side()
                      ->atomIs('Magicmethod')
                      ->outIs('NAME')
                      ->codeIs(array('__construct',
                                     '__destruct',
                                     ), self::TRANSLATE, self::CASE_INSENSITIVE)
             )
             ->not(
                 $this->side()
                      ->outIs('RETURNED')
                      ->atomIsNot('Void')
             )
             ->removeLink('RETURNTYPE')
             ->addAtom('Scalartypehint', array(
                             'fullcode'   => 'void',
                             'fullnspath' => '\\void',
                             'line'       => -1,
                             'extra'      => true,
                          ))
              ->addEFrom('RETURNTYPE', 'first')
              ->back('first')
              ->setProperty('typehint', '"one"');
        $this->prepareQuery();
    }
}

?>
