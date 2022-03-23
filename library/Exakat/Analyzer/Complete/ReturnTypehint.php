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

class ReturnTypehint extends Analyzer {
    public function analyze(): void {
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
                     ->raw('count().is(eq(1))')
             )
             ->outIs('RETURNED')
             ->atomIs('This')
             ->inIs('DEFINITION')
             ->atomIs('Class')

             ->as('theClass')
             ->back('first')
             ->raw(<<<'GREMLIN'
sideEffect(
        select("first").out("RETURNTYPE").fold().as("poubelle1").
        sideEffect( select("poubelle1").unfold().bothE().drop() )
    )
.addV('Identifier')
    .property("fullnspath", select("theClass").properties("fullnspath").value() )
    .property("fullcode", select("theClass").out("NAME").properties("fullcode").value() )
    .as("b")
.sideEffect(
    // Link Returntype to Function
    select("first").addE("RETURNTYPE").to("b")
)
GREMLIN
);
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
                     ->raw('count().is(eq(1))')
             )
             ->outIs('RETURNED')
             ->atomIs('New')
             ->outIs('NEW')
             ->has('fullnspath')
             ->as('theClass')
             ->raw(<<<'GREMLIN'
sideEffect(
        select("first").out("RETURNTYPE").fold().as("poubelle1").
        sideEffect( select("poubelle1").unfold().bothE().drop() )
    )
.addV('Identifier')
    .property("fullnspath", select("theClass").properties("fullnspath").value() )
    .property("fullcode", select("theClass").optional(out("NAME")).properties("fullcode").value() )
    .as("b")
.sideEffect(
    // Link Returntype to Function
    select("first").addE("RETURNTYPE").to("b")
)
GREMLIN
);
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
        );

        foreach($types as list($sources, $fqn, $fullcode)) {
            // type with integer
            $this->atomIs(self::FUNCTIONS_ALL)
                 ->filter(
                    $this->side()
                         ->outIs('RETURNTYPE')
                         ->atomIs('Void')
                 )
                 ->filter(
                    $this->side()
                         ->outIs('RETURNTYPE')
                         ->raw('count().is(eq(1))')
                 )
                 ->outIs('RETURNED')
                 ->atomIs($sources)
                 ->back('first')
                 ->raw(<<<GREMLIN
sideEffect(
        select("first").out("RETURNTYPE").fold().as("poubelle1").
        sideEffect( select("poubelle1").unfold().bothE().drop() )
    )
.addV('Scalartypehint')
         .property("fullcode", '$fullcode' )
         .property("fullnspath", '$fqn' )
         .as("b")
.sideEffect(
    // Link Returntype to Function
    select("first").addE("RETURNTYPE").to("b")
)
GREMLIN
    );
            $this->prepareQuery();
        }
    }
}

?>
