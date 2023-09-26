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

namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;
use Exakat\Data\Methods;
use Exakat\Query\DSL\FollowParAs;

class WrongTypeForNativeFunction extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // @todo : case for union and intersection types, and dnf
        // @todo add support for methods in stubs/ext/php
        // @todo better support for properties

        $types = array('int'    => array('Integer'),
                       'float'  => array('Integer', 'Float'),
                       'string' => self::STRINGS_LITERALS,
                       'array'  => array('Arrayliteral'),
                       'bool'   => array('Boolean', 'Bitoperation', 'Logical', 'Comparison'),
                      );

        $castTypes = array('float'  => array('T_DOUBLE_CAST', 'T_INT_CAST'),
                           'int'    => 'T_INT_CAST',
                           'string' => 'T_STRING_CAST',
                           'array'  => 'T_ARRAY_CAST',
                           'bool'   => 'T_BOOL_CAST',
                      );

        $returntypes = array();
        foreach ($types as $type => $atoms) {
            $returntypes[$type] = $this->readStubs('getFunctionsByReturnType', array($type ,   Methods::LOOSE));
        }
        $returntypes['null']    = $this->readStubs('getFunctionsByReturnType', array('null' ,  Methods::LOOSE));
        $returntypes['false']   = $this->readStubs('getFunctionsByReturnType', array('false' , Methods::LOOSE));
        ;

        $returnOtherTypes = array();
        foreach ($returntypes as $type => $functions) {
            $r2 = $returntypes;
            unset($r2[$type]);

            $returnOtherTypes[$type] = array_unique(array_merge(...array_values($r2)));
        }

        foreach ($types as $type => $atoms) {
            $list = $this->readStubs('getFunctionsByArgType', array($type, Methods::STRICT));
            $ini = array();
            foreach ($list as $l) {
                array_collect_by($ini, $l[0], $l[1]);
            }

            if (empty($ini)) {
                continue;
            }

            foreach ($ini as $rank => $functions) {
                if (empty($functions)) {
                    continue;
                }

                // class x { string $id; function foo() { array_map($this->id, '') ; }
                $this->atomFunctionIs($functions)
                     ->analyzerIsNot('self')
                     ->outWithRank('ARGUMENT', (int) $rank)
                     ->atomIs(array('Member', 'Staticproperty'))
                     ->inIs('DEFINITION')
                     ->atomIs('Propertydefinition')
                     ->inIs('PPP')
                     ->collectTypehints('typehints')
                     ->not(
                         $this->side()
                              ->outIs('TYPEHINT')
                              ->atomIs('Void')
                     )
                     ->raw('filter{!("\\\\' . $type . '" in typehints);}')
                     ->back('first');
                $this->prepareQuery();

                // foo($arg) { array_map($arg, '') ; }
                $this->atomFunctionIs($functions)
                     ->analyzerIsNot('self')
                     ->outWithRank('ARGUMENT', (int) $rank)
                     ->atomIs('Variable')
                     ->inIs('DEFINITION')
                     ->hasOut('TYPEHINT')
                     ->not(
                         $this->side()
                              ->outIs('TYPEHINT')
                              ->atomIs('Void')
                     )
                     ->collectTypehints('typehints')
                     ->raw('filter{!("\\\\' . $type . '" in typehints);}')
                     ->back('first');
                $this->prepareQuery();

                // array_map(STRING, '')
                // raw expressions
                $this->atomFunctionIs($functions)
                     ->analyzerIsNot('self')
                     ->outWithRank('ARGUMENT', $rank)
                     ->followParAs(FollowParAs::FOLLOW_NONE)
                     ->as('results')
                     ->atomIsNot($atoms, self::WITH_CONSTANTS)
                     ->not(
                         $this->side()
                              ->atomIs('Cast')
                              ->tokenIs($castTypes[$type])
                     )
                     ->back('results')
                     ->atomIsNot(array_merge(self::CALLS, self::CONTAINERS, array('Void', 'Identifier', 'Nsname', 'Staticconstant', 'Coalesce', 'Ternary')))
                     ->back('first');
                $this->prepareQuery();

                // native functions
                // substr(rand(), 1)
                $this->atomFunctionIs($functions)
                     ->analyzerIsNot('self')
                     ->outWithRank('ARGUMENT', (int) $rank)
                     ->as('results')
                     ->followParAs(FollowParAs::FOLLOW_NONE)
                     ->atomIs('Functioncall', self::WITH_VARIABLES)
                     ->is('isPhp', true)
                     ->fullnspathIs($returnOtherTypes[$type])

                     // Special case for false, inside a ?:
                     ->not(
                         $this->side()
                              ->fullnspathIs($returntypes['bool'] ?? array())
                              ->inIs('CONDITION')
                              ->atomIs('Ternary')
                              ->outIs('THEN')
                              ->atomIs('Void')
                     )

                     // Special case for null, inside a ??
                     ->not(
                         $this->side()
                              ->fullnspathIs($returntypes['null'])
                              ->inIs('LEFT')
                              ->atomIs('Coalesce')
                     )
                     ->back('results')
                     ->atomIsNot(array_merge(self::CONTAINERS, array('Void', 'Identifier', 'Nsname')))
                     ->back('first');
                $this->prepareQuery();

                // native functions
                // foo(array $array) { substr($array, 1); }
                $this->atomFunctionIs($functions)
                     ->analyzerIsNot('self')
                     ->outWithRank('ARGUMENT', (int) $rank)
                     ->as('results')
                     ->followParAs(FollowParAs::FOLLOW_NONE)
                     ->atomIs('Variable', self::WITH_VARIABLES)
                     ->goToTypehint()
                     ->atomIsNot('Void')
                     ->fullnspathIsNot('\\' . $type)
                     ->back('first');
                $this->prepareQuery();

                // custom functions
                // function foo() : int {}; substr(foo(), 1)
                $this->atomFunctionIs($functions)
                     ->analyzerIsNot('self')
                     ->outWithRank('ARGUMENT', (int) $rank)
                     ->atomIs(self::CALLS, self::WITH_VARIABLES)
                     ->inIs('DEFINITION')
                     ->atomIs(self::FUNCTIONS_ALL)
                     ->outIs('RETURNTYPE')
                     ->atomIsNot('Void')
                     ->fullnspathIsNot('\\' . $type)
                     ->back('first');
                $this->prepareQuery();
            }
        }
    }
}

?>
