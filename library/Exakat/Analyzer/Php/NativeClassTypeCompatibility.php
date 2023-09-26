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
use Exakat\Query\DSL\InitVariable;

class NativeClassTypeCompatibility extends Analyzer {
    public function analyze(): void {
        // return type
        // extension, method, return

        // extract the actual list of classes => method => types
        $returns = $this->readStubs('getNativeMethodReturn');
        // @todo : reduce the size of $return with the list of classes/methods actually used

        if (!empty($returns)) {
            $this->atomIs(self::CLASSES_ALL)
                 ->initVariable('phplist', $returns, initVariable::TYPE_ARGUMENT)
                 ->outIs(array('IMPLEMENTS', 'EXTENDS'))
                 ->savePropertyAs('fullnspath', 'phpnative')
                 ->raw('filter{ phpnative in phplist.keySet(); }')
                 ->back('first')
                 ->goToAllChildren(self::INCLUDE_SELF)

                 ->outIs('METHOD')
                 ->analyzerIsNot('self')
                 ->as('results')
                // Attribute returntypewillchange verificaiton
                 ->not(
                     $this->side()
                          ->outIs('ATTRIBUTE')
                          ->fullnspathIs('\returntypewillchange')
                 )
                 ->outIs('NAME')
                 ->savePropertyAs('fullcode', 'method')
                 ->raw('filter{ phplist[phpnative] != null && method.toLowerCase() in phplist[phpnative].keySet(); }')
                 ->back('results')

                 ->outIs('RETURNTYPE')
                 ->raw('filter{ it.get().label() == "Void" || !(it.get().property("fullnspath").value() in phplist[phpnative][method.toLowerCase()]); }')

                 ->back('results');
            $this->prepareQuery();
        }

        // extract the actual list of classes => method => types
        $returns = $this->readStubs('getNativeMethodArgType');
        if (!empty($returns)) {
            $this->atomIs(self::CLASSES_ALL)
                 ->initVariable('phplist', $returns, initVariable::TYPE_ARGUMENT)
                 ->outIs(array('IMPLEMENTS', 'EXTENDS'))
                 ->savePropertyAs('fullnspath', 'phpnative')
                 ->raw('filter{ phpnative in phplist.keySet(); }')
                 ->back('first')
                 ->goToAllChildren(self::INCLUDE_SELF)

                 ->outIs('METHOD')
                 ->analyzerIsNot('self')
                 ->as('results')
                // Attribute returntypewillchange verificaiton : this may not apply to arguments though
                 ->not(
                     $this->side()
                          ->outIs('ATTRIBUTE')
                          ->fullnspathIs('\returntypewillchange')
                 )
                 ->outIs('NAME')
                 ->savePropertyAs('fullcode', 'method')
                 ->raw('filter{ method.toLowerCase() in phplist[phpnative].keySet(); }')
                 ->back('results')

                 ->outIs('ARGUMENT')
                 ->savePropertyAs('rank', 'ranked')
                 ->outIs('TYPEHINT')
                 ->raw('filter{ it.get().label() == "Void" || !(it.get().property("fullnspath").value() in phplist[phpnative][method.toLowerCase()][0]); }')

                 ->back('results');
            $this->prepareQuery();
        }
    }
}

?>
