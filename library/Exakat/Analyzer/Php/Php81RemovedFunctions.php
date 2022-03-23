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

class Php81RemovedFunctions extends Analyzer {
    protected $phpVersion = '8.1-';

    public function analyze(): void {
        //image2wbmp()
        $functions = array('date_sunrise',
                           'date_sunset',
                           'strptime',
                           'strftime',
                           'gmtstrftime',
                           'odbc_result_all',
                           'mysqli_init',
                           'mhash',
                           'mhash_count',
                           'mhash_get_block_size',
                           'mhash_get_hash_name',
                           'mhash_keygen_s2k',
                           );

        $this->atomFunctionIs(makeFullnspath($functions))
             ->outIs('NAME')
             ->savePropertyAs('fullcode', 'fnp')
             ->back('first')
             ->not(
                $this->side()
                     ->goToInstruction('Ifthen')
                     ->outIs('CONDITION')
                     ->functioncallIs('\\function_exists')
                     ->outWithRank('ARGUMENT', 0)
                     ->atomIs('String')
                     ->noDelimiterIs('fnp')
             );
        $this->prepareQuery();
    }
}

?>
