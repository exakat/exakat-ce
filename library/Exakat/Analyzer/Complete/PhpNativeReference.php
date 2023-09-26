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

namespace Exakat\Analyzer\Complete;

class PhpNativeReference extends Complete {
    public function analyze(): void {
        // PHP functions that are using references
        $functions = $this->readStubs('getFunctionsReferenceArgs');

        $called = array_flip($this->called->getCalledFunctions());

        $references = array();
        foreach ($functions as $function) {
            $fnp = makeFullNsPath($function['function']);

            if (!isset($fnp)) {
                continue;
            }
            array_collect_by($references, $fnp, $function['position']);
        }

        //sort($a);
        $this->atomFunctionIs(array_keys($references))
              ->savePropertyAs('fullnspath', 'fnp')
              ->outIs('ARGUMENT')
              ->isNot('isModified', true)
              ->isHash('rank', $references, 'fnp')
              ->atomIs(self::CONTAINERS)
              ->setProperty('isModified', true);
        $this->prepareQuery();

        // @todo : methods? static or normal?
        $list = $this->readStubs('getMethodsReferenceArgs');
        $methods = array();
        $ranks = array();

        foreach ($list as $l) {
            array_collect_by($methods, $l['class'], $l['method']);
            array_collect_by($ranks, $l['method'], $l['position']);
        }

        //$mysqli_stmt->bind_result($a);
        $this->atomIs('Methodcall')
             ->outIs('OBJECT')
             ->goToTypehint()
             ->fullnspathIs(array_keys($methods))
             ->savePropertyAs('fullnspath', 'theClass')
             ->back('first')

             ->outIs('METHOD')
             ->outIs('NAME')
             ->isHash('fullcode', $methods, 'theClass')
             ->savePropertyAs('fullcode', 'fc')
             ->inIs('NAME')

             ->outIs('ARGUMENT')
             ->isHash('rank', $ranks, 'fc', self::CASE_SENSITIVE)
             ->setProperty('isModified', true);
        $this->prepareQuery();

        //mysqli_stmt::bind_result($a);
        // Not tested... Needs example
        $this->atomIs('Staticmethodcall')
             ->outIs('CLASS')
             ->fullnspathIs(array_keys($methods))
             ->savePropertyAs('fullnspath', 'theClass')
             ->back('first')

             ->outIs('METHOD')
             ->outIs('NAME')
             ->isHash('fullcode', $methods, 'theClass')
             ->savePropertyAs('fullcode', 'fc')
             ->inIs('NAME')

             ->outIs('ARGUMENT')
             ->isHash('rank', $ranks, 'fc', self::CASE_SENSITIVE)
             ->setProperty('isModified', true);
        $this->prepareQuery();
    }
}

?>
