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

namespace Exakat\Tasks\LoadFinal;

use Exakat\Analyzer\Analyzer;
use Exakat\Data\Methods;

class FinishIsModified extends LoadFinal {
    protected Methods $methods;

    public function __construct() {
        parent::__construct();

        $this->methods = exakat('methods');
    }

    public function run(): void {
        $variables = array('Variable',
                           'Variableobject',
                           'Variablearray',
                           'Array',
                           'Member',
                           'Staticproperty',
                           'Phpvariable',
                          );

        // No support for old style constructors
        $query = $this->newQuery('isModified with New');
        $query->atomIs('New', Analyzer::WITHOUT_CONSTANTS)
              ->inIs('DEFINITION')
              ->outIs('ARGUMENT')
              ->is('reference', true)
              ->savePropertyAs('rank', 'r')
              ->back('first')
              ->outIs('NEW')
              ->outIs('ARGUMENT')
              ->samePropertyAs('rank', 'r', Analyzer::CASE_SENSITIVE)
              ->atomIs($variables, Analyzer::WITHOUT_CONSTANTS)
              ->setProperty('isModified', true)
              ->returnCount();
        $query->prepareRawQuery();
        if ($query->canSkip()) {
            $countNew = 0;
        } else {
            $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
            $countNew = $result->toInt();
        }

        $query = $this->newQuery('isModified with function calls');
        $query->atomIs(array('Functioncall', 'Methodcall', 'Staticmethodcall'), Analyzer::WITHOUT_CONSTANTS)
              ->inIs('DEFINITION')
              ->outIs('ARGUMENT')
              ->is('reference', true)
              ->savePropertyAs('rank', 'r')
              ->back('first')
              ->outIsIE('METHOD')
              ->outIs('ARGUMENT')
              ->samePropertyAs('rank', 'r', Analyzer::CASE_INSENSITIVE)
              ->atomIs($variables, Analyzer::WITHOUT_CONSTANTS)
              ->setProperty('isModified', true)
              ->returnCount();
        $query->prepareRawQuery();
        if ($query->canSkip()) {
            $countFunction = 0;
        } else {
            $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
            $countFunction = $result->toInt();
        }

        $count = $countNew + $countFunction;
        display("Created $count isModified values");

        $query = $this->newQuery('isModified with list() or foreach()');
        $query->atomIs('Keyvalue', Analyzer::WITHOUT_CONSTANTS)
              ->inIs(array('VALUE', 'ARGUMENT'))
              ->atomIs(array('Foreach', 'List'), Analyzer::WITHOUT_CONSTANTS)
              ->back('first')
              ->outIs(array('INDEX', 'VALUE'))
              ->atomIs($variables, Analyzer::WITHOUT_CONSTANTS)
              ->setProperty('isModified', true)
              ->returnCount();
        $query->prepareRawQuery();
        if ($query->canSkip()) {
            $countOperator = 0;
        } else {
            $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
            $countOperator = $result->toInt();
        }

        $count = $countFunction + $countOperator;
        display("Created $count isModified values with => ");
    }
}

?>
