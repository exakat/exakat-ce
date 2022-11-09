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

namespace Exakat\Analyzer\Dump;


class CollectBlockSize extends AnalyzerTable {
    protected string $analyzerName = 'blockSize';

    protected string $analyzerTable = 'blockSize';

    // Store inclusionss of files within each other
    protected string $analyzerSQLTable = <<<'SQL'
CREATE TABLE blockSize (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                            type STRING,
                            size STRING
                        )
SQL;

    public function analyze(): void {
        $this ->atomIs(array('Dowhile', 'While', 'Ifthen', 'Foreach', 'For'), self::WITHOUT_CONSTANTS)
              ->raw('sideEffect{ l = it.get().label();}')
              ->outIs(array('BLOCK', 'THEN', 'ELSE'))
              ->raw('sideEffect{ if (l == "Ifthen") { l = "Ifthen"; }}')
              ->raw(<<<'GREMLIN'
sideEffect{ x = it.get().value("line"); y = x;}
     .where(
        __.out("EXPRESSION")
          .sideEffect{ if (it.get().value("line") > y) { y = it.get().value("line");}}
          .fold()
     )
     .map{ [ "type": l, "size": y - x]};
GREMLIN
              );
        $this->prepareQuery();
    }
}
