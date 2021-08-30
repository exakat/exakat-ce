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

class ArrayMapPassesByValue extends Analyzer {
    /* PHP version restrictions
    protected $phpVersion = '7.4-';
    */

    public function analyze(): void {
        // array_map('foo', $a)
        // function foo(&$arg) {}
        $this->atomFunctionIs('\\array_map')
             ->outWithRank('ARGUMENT', 0)
             ->optional(  // processes closures and arrow functions in place
                $this->side()
                     ->inIs('DEFINITION')
             )
             ->outWithRank('ARGUMENT', 0)
             ->is('reference', true)
             ->back('first');
        $this->prepareQuery();

        $phpNative = $this->methods->getFunctionsReferenceArgs();
        $positions = array();
        foreach($phpNative as $function) {
            array_collect_by($positions, $function['position'], mb_strtolower($function['function']));
        }

        // array_map('foo', $a)
        // function foo(&$arg) {}
        $this->atomFunctionIs('\\array_map')
             ->outWithRank('ARGUMENT', 0)
             ->is('isPhp', true)
             ->noDelimiterIs($positions[0])
             ->back('first');
        $this->prepareQuery();
    }
}

?>
