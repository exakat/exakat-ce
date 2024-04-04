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


namespace Exakat\Analyzer\Functions;

use Exakat\Analyzer\Analyzer;

class UsesDefaultArguments extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/SetClassRemoteDefinitionWithTypehint',
                     'Complete/SetClassRemoteDefinitionWithGlobal',
                     'Complete/SetClassRemoteDefinitionWithInjection',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                     'Complete/SetClassRemoteDefinitionWithParenthesis',
                     'Complete/SetClassRemoteDefinitionWithReturnTypehint',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                     'Complete/VariableTypehint',
                    );
    }
    // $c = count($x);
    //PHP native function : count($array, $options)
    public function analyze(): void {
        // case for functions
        $functions = $this->readStubs('getFunctionsArgsInterval');

        $positions = array();
        foreach ($functions as $function) {
            // No default argument, so just skip them
            if ($function['args_min'] === $function['args_max']) {
                continue;
            }
            if ($function['args_max'] === \MAX_ARGS) {
                continue;
            }
            // Only test if the last is missing. This is sufficient
            $positions[makeFullnspath($function['name'])] = $function['args_max'];
        }

        $this->atomFunctionIs(array_keys($positions))
             ->savePropertyAs('fullnspath', 'fnp')
             ->isLessHash('count', $positions, 'fnp');
        $this->prepareQuery();

        // in-code definitions
        $this->atomIs(array('Function', 'Method'))
             ->savePropertyAs('args_max', 'c')
             ->back('first')
             ->outIs('DEFINITION')
             ->atomIs(self::CALLS)
             ->outIsIE('METHOD')
             ->analyzerIsNot('self')
             ->atomIsNot('Virtualproperty')
             ->isLess('count', 'c')
             ->inIsIE('METHOD');
        $this->prepareQuery();

        // @todo : same for methods : static + normal
        // @todo : local definitions? methods, functions, closures/fn
    }
}

?>
