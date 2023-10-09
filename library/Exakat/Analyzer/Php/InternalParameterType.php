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

class InternalParameterType extends Analyzer {
    public function analyze(): void {
        $args = exakat('methods')->getInternalParameterType();

        $typeConversion = array('string'   => array('Magicconstant', 'Heredoc', 'String'),
                                'float'    => 'Float',
                                'int'      => 'Integer',
                                'numeric'  => array('Float', 'Integer'),
                                'resource' => '',
                                'bool'     => 'Boolean',
                                'array'    => 'Arrayliteral',
                                'void'     => 'Void',
                                'mixed'    => '' //explicitely here to avoid it
                                );

        foreach ($args as $position => $types) {
            foreach ($types as $type => $functions) {
                if (str_contains($type, ',')  ) {
                    continue; // No support for multiple type yet
                }

                if (!isset($typeConversion[$type]) || empty($typeConversion[$type])) {
                    continue;
                }

                $this->atomFunctionIs($functions)
                     ->analyzerIsNot('self')
                     ->isAnyOf(array('isPhp', 'isExt'), true)
                     ->outWithRank('ARGUMENT', $position)

                     // only include literals (and closures and literal array)
                     ->atomIs(array('Integer', 'String', 'Arrayliteral', 'Float', 'Boolean', 'Null', 'Integer', 'Closure'), self::WITH_CONSTANTS)

                    // Constant (Identifier), logical, concatenation, addition ?
                    // Those will have to be replaced after more research

                    // All string equivalents
                     ->atomIsNot($typeConversion[$type])
                     ->back('first');
                $this->prepareQuery();
            }
        }
    }
}

?>
