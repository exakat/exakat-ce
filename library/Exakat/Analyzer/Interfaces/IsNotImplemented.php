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

namespace Exakat\Analyzer\Interfaces;

use Exakat\Analyzer\Analyzer;
use Exakat\Query\DSL\CollectMethods;

class IsNotImplemented extends Analyzer {
    public function analyze(): void {
        // interface i { function i() {}}
        // class c implements i {       }
        $this->atomIs('Interface')
             ->collectMethods('interfaceMethods')
             ->raw('filter{interfaceMethods.size() > 0;}')
             ->goToAllChildren(self::EXCLUDE_SELF)
             ->atomIs(self::CLASSES_ALL)
             ->isNot('abstract', true)
             ->collectMethods('classMethods', CollectMethods::METHOD_CONCRETE)
             ->raw('filter{interfaceMethods.size() > classMethods.size() || !classMethods.containsAll(interfaceMethods);}');
        $this->prepareQuery();

        // todo : check for cross over interfaces
        // warning : methods may be spread over multiple classes
        // interface i { i1, i2} class a { function i1()} class b implement i { function i2()}
    }
}

?>
