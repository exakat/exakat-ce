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

class FinishPppTypehint extends LoadFinal {
    public function run(): void {
        $query = $this->newQuery('Put typehints to Propertydefinition');
        $query->atomIs(array('Propertydefinition'), Analyzer::WITHOUT_CONSTANTS)
            // Reach first parent
              ->inIs('PPP')
              ->savePropertyAs('typehint', 'tp')
              ->back('first')
              ->setProperty('typehint', 'tp')

              ->inIs('PPP')
              ->outIs('TYPEHINT')
              ->addEFrom('TYPEHINT', 'first', array('extra' => 'true'))
              ->returnCount();
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $countTypehint = $result->toInt();
        display("Created $countTypehint added from Propertydefinition");
    }
}

?>
