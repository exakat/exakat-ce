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

namespace Exakat\Analyzer\Interfaces;

use Exakat\Analyzer\Analyzer;
use Exakat\Query\DSL\CollectMethods;
use Exakat\Data\Dictionary;

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

        $list = $this->readStubs('getInterfaceMethodsNameAndCount');
        foreach ($list as &$l) {
            $l = $this->dictCode->translate(array_column($l, 'name'), Dictionary::CASE_INSENSITIVE);
        }
        unset($l);
        $list = array_filter($list);

        $this->atomIs('Class')
             ->outIs('IMPLEMENTS')
             ->isAnyOf(array('isPhp', 'isStub', 'isExt'), true)
             ->fullnspathIs(array_keys($list))
             ->savePropertyAs('fullnspath', 'fqn')
             ->back('first')
             ->isNot('abstract', true)
             ->collectMethods('classMethods', CollectMethods::METHOD_CONCRETE)
             ->raw('filter{x = ***; x[fqn].size() > classMethods.size() || x[fqn].intersect(classMethods).size() != x[fqn].size();}',$list);
        $this->prepareQuery();
    }
}

?>