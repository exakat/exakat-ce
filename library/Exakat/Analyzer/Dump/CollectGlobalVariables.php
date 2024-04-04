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

class CollectGlobalVariables extends AnalyzerTable {
    protected string $analyzerName = 'globalVariables';

    protected string $analyzerTable = 'globalVariables';

    // Store inclusionss of files within each other
    protected string $analyzerSQLTable = <<<'SQL'
CREATE TABLE globalVariables ( id INTEGER PRIMARY KEY AUTOINCREMENT,
                               variable STRING,
                               file STRING,
                               line INTEGER,
                               isRead INTEGER,
                               isModified INTEGER,
                               type STRING
                             )
SQL;

    public function dependsOn(): array {
        return array('Complete/Superglobals',
                     'Complete/GlobalDefinitions',
                    );
    }

    public function analyze(): void {
        $this->atomIs(array('Virtualglobal', 'Global'), self::WITHOUT_CONSTANTS)
             ->fullcodeIsNot('$GLOBALS')
             ->outIs('DEFINITION')
             ->savePropertyAs('label', 'type')
             ->outIsIE('DEFINITION')
             ->as('variable')
             ->goToInstruction('File')
             ->savePropertyAs('fullcode', 'file')
             ->back('variable')
             ->savePropertyAs('line', 'ligne')
             ->savePropertyAs('fullcode', 'variable')
             ->savePropertyAs('isRead', 'isRead')
             ->savePropertyAs('isModified', 'isModified')
             ->raw(<<<'GREMLIN'
coalesce(
    __.hasLabel("Array").sideEffect{ type = "\$GLOBALS"; },
    __.where(__.in("GLOBAL")).sideEffect{ type = "explicit"; },
    __.sideEffect{ type = "implicit"; }
)
GREMLIN
             )
             ->getVariable(array('variable', 'file', 'ligne', 'isRead', 'isModified', 'type'));
        $this->prepareQuery();
    }
}

?>
