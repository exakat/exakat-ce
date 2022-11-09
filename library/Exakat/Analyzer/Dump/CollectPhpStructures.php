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

// @todo make the same for stubs and ext
class CollectPhpStructures extends AnalyzerTable {
    protected string $analyzerName = 'phpStructures';

    protected string $analyzerTable = 'phpStructures';

    protected string $analyzerSQLTable = <<<'SQL'
CREATE TABLE phpStructures (id INTEGER PRIMARY KEY AUTOINCREMENT,
                            type STRING,
                            name STRING,
                            count INTEGER
)
SQL;

    public function dependsOn(): array {
        return array('Constants/ConstantUsage',
                     'Interfaces/InterfaceUsage',
                     'Traits/TraitUsage',
                     'Php/EnumUsage',
                     'Classes/ClassUsage',

                     'Complete/VariableTypehint', // This use to have a bug, so it is added for security
                    );
    }

    public function analyze(): void {
        $this->collectPhpFunctioncall(                                                                                 'function');
        $this->collectPhpStructuresWithAnalyzer(array('Identifier', 'Nsname'),            'Constants/ConstantUsage',   'constant');
        $this->collectPhpStructuresWithAnalyzer(array('Identifier', 'Nsname'),            'Interfaces/InterfaceUsage', 'interface');
        $this->collectPhpStructuresWithAnalyzer(array('Identifier', 'Nsname'),            'Traits/TraitUsage',         'trait');
        $this->collectPhpStructuresWithAnalyzer(array('Identifier', 'Nsname'),            'Php/EnumUsage',             'enum');
        $this->collectPhpStructuresWithAnalyzer(array('Newcall', 'Identifier', 'Nsname', 'Newcallname'), 'Classes/ClassUsage',        'class');
        // @todo make the same for global variables
    }

    private function collectPhpStructuresWithAnalyzer(array $label, string $analyzer, string $type): void {
        $this->atomIs($label)
             ->analyzerIs($analyzer)
             ->isAnyOf(array('isPhp', 'isExt'), true)
             ->raw('groupCount("m").by("fullnspath").cap("m").map{ x = []; for(key in it.get().keySet()) { x.add(["type":"' . $type . '", "name":key, "count":it.get().getAt(key)]);}; x }[0]');
        $this->prepareQuery();
    }

    private function collectPhpFunctioncall(string $type): void {
        $this->atomIs('Functioncall')
             ->isAnyOf(array('isPhp', 'isExt'), true)
             ->raw('groupCount("m").by("fullnspath").cap("m").map{ x = []; for(key in it.get().keySet()) { x.add(["type":"' . $type . '", "name":key, "count":it.get().getAt(key)]);}; x }[0]');
        $this->prepareQuery();
    }
}

?>
