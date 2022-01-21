<?php
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

namespace Exakat\Analyzer\Traits;

use Exakat\Analyzer\Analyzer;

class CannotCallTraitMethod extends Analyzer {
//    protected $phpVersion = '8.1-';

    /* List dependencies 
    public function dependsOn() : array {
        return array('Category/Analyzer',
                     '',
                    );
    }
    */
    
    public function analyze() : void {
        // trait t { static public function t() {}}
        // t::t();
        $this->atomIs('Staticmethodcall')
             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->atomIs('Trait')
             ->back('first');
        $this->prepareQuery();

        // trait t { static public $p;}
        // t::$p;
        $this->atomIs('Staticproperty')
             ->outIs('CLASS')
             ->inIs('DEFINITION')
             ->atomIs('Trait')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
