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

namespace Exakat\Analyzer\Dump;


class PublicReach extends AnalyzerTable {
    protected string $analyzerName = 'publicReach';

    protected string $analyzerTable = 'publicReach';

    protected string $analyzerSQLTable = <<<'SQL'
CREATE TABLE publicReach ( id INTEGER PRIMARY KEY AUTOINCREMENT,
                           calling STRING,
                           caller STRING,
                           class STRING
                         )
SQL;

    public function analyze(): void {
        // class x
        $this->atomIs('This')
             ->inIs('OBJECT')
             ->atomIs('Methodcall')
             ->outIs('METHOD')
             ->outIs('NAME')
             ->as('called')

             ->goToFunction()
             ->outIs('NAME')
             ->as('caller')

             ->back('first')
             ->inIs('DEFINITION')
             ->atomIs('Class')
             ->as('context')

             ->select(array('called'  => 'fullcode',
                            'caller'  => 'fullcode',
                            'context' => 'fullnspath',
                            ))
             ->unique();
        $this->prepareQuery();
    }
}

?>
