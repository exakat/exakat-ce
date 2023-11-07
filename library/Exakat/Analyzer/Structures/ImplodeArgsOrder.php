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

namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class ImplodeArgsOrder extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/VariableTypehint',
                     'Variables/IsLocalConstant',
                    );
    }

    public function analyze(): void {
        $functions = array('\\join', '\\implode');

        // detect an array in first arg
        // Constants
        $this->atomFunctionIs($functions)
             ->is('count', 2)
             ->analyzerIsNot('self')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('Arrayliteral', self::WITH_CONSTANTS)
             ->back('first');
        $this->prepareQuery();

        // Local variable, 
        $this->atomFunctionIs($functions)
             ->is('count', 2)
             ->analyzerIsNot('self')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs(array('Variable', 'Member', 'Staticproperty', 'Functioncall', 'Methodcall', 'Staticmethodcall'))
             ->getType('type')
             ->sameVariableAs('type', '\\array')
             ->back('first');
        $this->prepareQuery();

        // detect an array in second arg
        // Constants
        $this->atomFunctionIs($functions)
             ->is('count', 2)
             ->analyzerIsNot('self')
             ->outWithRank('ARGUMENT', 1)
             ->atomIs(self::STRINGS_ALL, self::WITH_CONSTANTS)
             ->back('first');
        $this->prepareQuery();

        // Local variable, 
        $this->atomFunctionIs($functions)
             ->is('count', 2)
             ->analyzerIsNot('self')
             ->outWithRank('ARGUMENT', 1)
             ->atomIs(array('Variable', 'Member', 'Staticproperty', 'Functioncall', 'Methodcall', 'Staticmethodcall'))
             ->getType('type')
             ->sameVariableAs('type', '\\string')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
