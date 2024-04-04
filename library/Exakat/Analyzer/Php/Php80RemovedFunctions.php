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


namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;

class Php80RemovedFunctions extends Analyzer {
    protected string $phpVersion = '8.0-';

    public function analyze(): void {
        //image2wbmp()
        $functions = array('image2wbmp',
                           'png2wbmp',
                           'jpeg2wbmp',
                           'ldap_sort',
                           'hebrevc',
                           'convert_cyr_string',
                           'ezmlm_hash',
                           'money_format',
                           'get_magic_quotes_gpc',
                           'get_magic_quotes_gpc_runtime',
                           'create_function',
                           'each',
                           'read_exif_data',
                           'gmp_random',
                           'fgetss',
                           'restore_include_path',
                           'gzgetss',
                           'mbregex_encoding',
                           'mbereg',
                           'mberegi',
                           'mbereg_replace',
                           'mberegi_replace',
                           'mbsplit',
                           'mbereg_match',
                           'mbereg_search',
                           'mbereg_search_pos',
                           'mbereg_search_regs',
                           'mbereg_search_init',
                           'mbereg_search_getregs',
                           'mbereg_search_getpos',
                           'mbereg_search_setpos',
                           );

        $this->atomFunctionIs(makeFullNsPath($functions))
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
