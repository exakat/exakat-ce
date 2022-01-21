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

namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class UnsupportedTypesWithOperators extends Analyzer {
    public function dependsOn(): array {
        return array('Variables/IsLocalConstant',
                     'Complete/CreateDefaultValues',
                    );
    }

    public function analyze(): void {
//+, -, *, /, **, %, <<, >>, &, |, ^, ~, ++, --

        $atoms = array('Addition',
                       'Multiplication',
                       'Bitoperation',
                       'Bitshift',
                       'Not',
                       'Power',
                       'Postplusplus',
                       'Preplusplus',
                       'Array',

                       'Keyvalue',
                       );

        $links = array('LEFT',
                       'RIGHT',
                       'NOT',
                       'PREPLUSPLUS',
                       'POSTPLUSPLUS',

                       'INDEX',
                       );

        // clone $a * 3
        $this->atomIs($atoms)
             ->tokenIsNot('T_PLUS') // all but Addition +
             ->outIs($links)
             ->atomIs(array('Arrayliteral', 'New', 'Clone'), self::WITH_CONSTANTS)
             ->back('first');
        $this->prepareQuery();

        $this->atomIs($atoms)
             ->analyzerIsNot('self')
             ->tokenIsNot('T_PLUS') // all but Addition +
             ->outIs($links)
             ->atomIs('Cast', self::WITH_CONSTANTS)
             ->tokenIs('T_ARRAY_CAST') // no cast to resource
             ->back('first');
        $this->prepareQuery();

        // todo : functioncall that return array or resource

        // array() + 3
        $this->atomIs('Addition')
             ->tokenIs('T_PLUS')
             ->outIs('LEFT')
             ->atomIs(array('Arrayliteral', 'Cast'), self::WITH_CONSTANTS)
             ->tokenIsNot(array('T_INT_CAST', 'T_DOUBLE_CAST', 'T_OBJECT_CAST', 'T_BOOL_CAST', 'T_STRING_CAST'))
             ->back('first')
             ->outIs('RIGHT')
             ->atomIsNot(array('Arrayliteral'), self::WITH_CONSTANTS)
             ->tokenIsNot('T_ARRAY_CAST')
             ->atomIsNot(self::CONTAINERS)
             ->atomIsNot(self::CALLS)
             ->atomIsNot(array('Identifier', 'Nsname'))
             ->back('first');
        $this->prepareQuery();

        // foo() : array() * 3
        $this->atomIs($atoms)
             ->outIs($links)
             ->atomIs('Functioncall', self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->outIs('RETURNTYPE')
             ->atomIs('Scalartypehint')
             ->fullnspathIs(array('\\array', '\\resource'))
             ->back('first');
        $this->prepareQuery();

        // foo() : A\B * 3
        $this->atomIs($atoms)
             ->outIs($links)
             ->atomIs('Functioncall', self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->outIs('RETURNTYPE')
             ->atomIs(self::STATIC_NAMES)
             ->back('first');
        $this->prepareQuery();

        // ~array()
        $this->atomIs(array('Not', 'Bitoperation'))
             ->analyzerIsNot('self')
             ->tokenIsNot('T_BANG') // ~  and ! are the same Atom
             ->outIs($links)
             ->atomIs('Functioncall', self::WITH_CONSTANTS)
             ->inIs('DEFINITION')
             ->outIs('RETURNTYPE')
             ->atomIsNot('Void')
             ->fullnspathIsNot('\\bool')
             ->back('first');
        $this->prepareQuery();

        // todo : PHP native functions
        // todo : Typed arguments and properties
        // todo : typed constants (via its value)

    }
}

?>
