<?php declare(strict_types = 1);
/*
 * Copyright 2012-2021 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


class CollectDependencyExtension extends AnalyzerTable {
    protected $analyzerName = 'dependenciesExtensions';

    protected $analyzerTable = 'dependenciesExtensions';

    protected $analyzerSQLTable = <<<'SQL'
CREATE TABLE dependenciesExtensions ( id INTEGER PRIMARY KEY AUTOINCREMENT,
                                      origin STRING,
                                      extending STRING
                                     )
SQL;

    public function analyze(): void {
        // class x
        $this->atomIs(array('Class', 'Interface'))
             ->goToAllImplements(self::INCLUDE_SELF)
             ->outIs(array('EXTENDS', 'IMPLEMENTS'))

             ->hasNoIn('DEFINITION')
             ->isNot('isPhp', true)
             ->isNot('isExt', true)

             // No check for isStub
             ->_as('ancestor')
             ->select(array('first'    => 'fullnspath',
                            'ancestor' => 'fullnspath',
                            ));
        $this->prepareQuery();
    }
}

?>
