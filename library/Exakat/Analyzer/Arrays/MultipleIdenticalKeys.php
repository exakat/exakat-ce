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


namespace Exakat\Analyzer\Arrays;

use Exakat\Analyzer\Analyzer;

class MultipleIdenticalKeys extends Analyzer {
    protected int $arrayMaxSize = 15000;

    public function dependsOn(): array {
        return array('Complete/PropagateConstants',
                    );
    }

    public function analyze(): void {
        // array('a' => 1, 'b' = 2)
        $this->atomIs('Arrayliteral')
             ->is('constant', true)
             ->isLess('count', $this->arrayMaxSize)
             ->isMore('count', 1)

            // Check that the array has at least one key=>value pair
             ->filter(
                 $this->side()
                      ->outIs('ARGUMENT')
                      ->atomIs('Keyvalue')
             )

             ->filter(
                 $this->side()
                      ->initVariable('counts', '[:]')
                      ->outIs('ARGUMENT')
                      ->optional(
                          $this->side()
                               ->atomIsNot('Keyvalue')
                               ->hasNoOut('INDEX')
                               ->savePropertyAs('rank', 'k')
                      )
                      ->not(
                          $this->side()
                               ->outIs('INDEX')
                               ->atomIs(array('Identifier', 'Nsname', 'Staticconstant'))
                               ->hasNoIn('DEFINITION')
                      )
                      ->optional(
                          $this->side()
                               ->atomIs('Keyvalue')
                               ->outIs('INDEX')
                               ->atomIs(array('String', 'Heredoc', 'Concatenation', 'Integer', 'Float', 'Boolean', 'Null', 'Staticclass'), self::WITH_CONSTANTS)
                               ->atomIsNot(array('Identifier'))
                               ->raw('or(has("intval"), has("noDelimiter"))')
                               ->raw(<<<'GREMLIN'
filter{ 
    if (it.get().label() in ["String", "Heredoc", "Concatenation", "Staticclass", "Null"] ) { 
        k = it.get().value("noDelimiter"); 
        if (k.isInteger()) {
            k = k.toInteger();
            
            if (k.toString().length() != it.get().value("noDelimiter").length()) {
                k = it.get().value("noDelimiter"); 
            }
        }
        true;
    } 
    else if (it.get().label() in ["Integer", "Float", "Boolean"] ) {  
        k = it.get().value("intval"); 
        true;
    }  else {
        k = null;
        false;
    }
}
GREMLIN
                               )
                      )
                     ->raw(<<<'GREMLIN'
sideEffect{
    if (counts[k] == null) { 
        counts[k] = 1; 
    } else { 
        counts[k]++; 
    }
}

.filter{ (counts.values().sum() > 1) && (counts.findAll{it.value > 1}.size() > 0); }
GREMLIN
                     )
             )

             ->back('first');
        $this->prepareQuery();
    }
}

?>