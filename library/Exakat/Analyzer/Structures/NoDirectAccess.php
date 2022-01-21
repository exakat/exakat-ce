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

class NoDirectAccess extends Analyzer {
    public function analyze(): void {
        //defined('AJXP_EXEC') or die('Access not allowed'); : Constant used!
        $this->atomIs('Logical')
             ->tokenIs(array('T_BOOLEAN_AND', 'T_BOOLEAN_OR', 'T_LOGICAL_AND', 'T_LOGICAL_OR'))
             // find !defined and defined
             ->atomInsideNoDefinition('Functioncall')
             ->functioncallIs('\\defined')
             ->back('first')
             ->atomInsideNoDefinition('Exit')
             ->back('first');
        $this->prepareQuery();

        //if(!defined('CMS')) die/exit
        $this->atomIs('Ifthen')
             ->outIs('CONDITION')
             // find !defined and defined
             ->atomIs('Not')
             ->outIs('NOT')
             ->atomIs('Functioncall')
             ->functioncallIs('\\defined')
             ->back('first')
             ->outIs('THEN')
             ->atomInsideNoDefinition('Exit')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('Ifthen')
             ->outIs('CONDITION')
             // find !defined and defined
             ->atomIs('Functioncall')
             ->functioncallIs('\\defined')
             ->back('first')
             ->outIs('THEN')
             ->atomInsideNoDefinition('Exit')
             ->back('first');
        $this->prepareQuery();

        //if (defined('_ECRIRE_INC_VERSION')) return;
        $this->atomIs('Ifthen')
             ->outIs('CONDITION')
             ->atomIs('Functioncall')
             ->functioncallIs('\\defined')
             ->back('first')
             ->outIs('THEN')
             ->outWithRank('EXPRESSION', 0)
             ->atomIs('Return')
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('Ifthen')
             ->outIs('CONDITION')
             ->atomIs('Not')
             ->outIs('NOT')
             ->atomIs('Functioncall')
             ->functioncallIs('\\defined')
             ->back('first')
             ->outIs('THEN')
             ->outWithRank('EXPRESSION', 0)
             ->atomIs('Return')
             ->back('first');
        $this->prepareQuery();

        //if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");
        $this->atomIs('Ifthen')
             ->outIs('CONDITION')
             ->functioncallIs(array('\\stristr', '\\strstr'))
             ->back('first')
             ->outIs('THEN')
             ->outWithRank('EXPRESSION', 0)
             ->atomIs('Exit')
             ->back('first');
        $this->prepareQuery();

    }
}

?>
