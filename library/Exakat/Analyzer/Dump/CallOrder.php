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

class CallOrder extends AnalyzerTable {
    protected string $analyzerName = 'callOrder';

    protected string $analyzerTable = 'callOrder';

    // Store calls of methods : origin and call
    protected string $analyzerSQLTable = <<<'SQL'
CREATE TABLE callOrder ( id INTEGER PRIMARY KEY AUTOINCREMENT,
                         calling STRING,
                         callingName STRING,
                         called STRING,
                         calledName STRING,
                         CONSTRAINT "unique" UNIQUE (calling, called)  ON CONFLICT IGNORE
                        )
SQL;

    public function dependsOn(): array {
        return array('Complete/SetClassRemoteDefinitionWithTypehint',
                     'Complete/SetClassRemoteDefinitionWithGlobal',
                     'Complete/SetClassRemoteDefinitionWithInjection',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                     'Complete/SetClassRemoteDefinitionWithParenthesis',
                     'Complete/SetClassRemoteDefinitionWithReturnTypehint',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                     'Complete/OverwrittenMethods',
                     'Complete/SetParentDefinition',
                     'Complete/CreateMagicProperty',
                    );
    }

    public function analyze(): void {
        // function foo() { a::C(); }
        $calls = array_merge(self::FUNCTIONS_CALLS,
            array(	'Member',
                   'Staticproperty',
            )
        );

        $this ->atomIs($calls, self::WITHOUT_CONSTANTS)
              ->inIs('CALLED')
              ->atomIs(array('Function', 'Method', 'Magicmethod'))
              ->as('calling')
              ->as('callingName')
              ->back('first')
              ->inIs('DEFINITION')
              ->atomIs(array('Function', 'Method', 'Magicmethod'))
              ->has('fullnspath') // may end up on a virtualmethod
              ->as('called')
              ->as('calledName')
              ->select(array('calling'     => 'fullnspath',
                             'callingName' => 'fullcode',
                             'called'      => 'fullnspath',
                             'calledName'  => 'fullcode',
                             ));
        $this->prepareQuery();
    }
}

?>