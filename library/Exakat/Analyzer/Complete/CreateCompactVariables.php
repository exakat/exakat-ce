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

class CreateCompactVariables extends Complete {
    public function dependsOn(): array {
        return array('Variables/IsLocalConstant',
                    );
    }

    public function analyze(): void {
        // compact('a') : 'a' is one usage of a variable
        $this->atomFunctionIs('\compact')
              ->outIs('ARGUMENT')
              ->as('varInString')
              ->atomIs('String', self::WITH_CONSTANTS)
              ->savePropertyAs('noDelimiter', 'name')
              ->makeVariableName('name')

              ->back('varInString')
              ->goToInstruction(array('Function', 'Closure', 'Method', 'Magicmethod', 'File'))
              ->outIs(array('DEFINITION', 'ARGUMENT', 'USE'))
              ->atomIs(array('Variabledefinition', 'Globaldefinition', 'Staticdefinition', 'Parameter'), self::WITHOUT_CONSTANTS)
              ->samePropertyAs('fullcode', 'name', self::CASE_SENSITIVE)
              ->as('var')
              ->back('varInString')

              ->hasNoLinkYet('DEFINITION', 'var')
              ->addEFrom('DEFINITION', 'var');
        $this->prepareQuery();
    }
}

?>
