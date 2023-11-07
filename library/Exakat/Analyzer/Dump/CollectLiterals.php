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

use Exakat\Dump\Dump;

class CollectLiterals extends AnalyzerTable {
    protected string $analyzerName = 'Literal values';

    public function analyze(): void {
        $types = array('Integer', 'Float', 'String', 'Heredoc', 'Arrayliteral');

        foreach ($types as $type) {
            $this->analyzerTable = "literal$type";
            $this->analyzerSQLTable = <<<SQL
CREATE TABLE literal{$type} (  
                              id INTEGER PRIMARY KEY AUTOINCREMENT,
                              name STRING,
                              file STRING,
                              line INTEGER
                            )
SQL;

            $this->atomIs($type)
                 ->is('constant', true)
                 ->savePropertyAs('fullcode', 'name')
                 ->savePropertyAs('line', 'theLine')
                 ->goToFile()
                 ->savePropertyAs('fullcode', 'file')
                 ->getVariable(array('name' => 'name',
                 					 'file' => 'file',
                 					 'line' => 'theLine',
                 					 ));
            $this->prepareQuery();
        }

        $this->analyzerTable = 'stringEncodings';
        $this->analyzerSQLTable = <<<'SQL'
CREATE TABLE stringEncodings (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                                encoding STRING,
                                block STRING,
                                CONSTRAINT "encoding" UNIQUE (encoding, block)
                              )
SQL;

        // @todo 'Concatenation', 'Heredoc' too
        $this->atomIs('String')
             ->is('constant', true)
        	 ->savePropertyAs('encoding', 'theEncoding')
        	 ->savePropertyAs('block', 'theBlock')
             ->getVariable(array('encoding' => 'theEncoding',
                 				 'block' => 'theBlock',
                 			))
              ->unique();
        $this->prepareQuery();
    }

    public function getDump(): array {
        if (!$this->hasResults()) {
            return array();
        }

        $report = array();

        $dump = Dump::factory($this->config->dump);
        $r = $dump->fetchTable('literalString', array('name'));
        $report['string'] = $r->toArray();

        $r = $dump->fetchTable('literalInteger', array('name'));
        $report['integer'] = $r->toArray();

        $r = $dump->fetchTable('literalFloat', array('name'));
        $report['float'] = $r->toArray();

        $r = $dump->fetchTable('literalHeredoc', array('name'));
        $report['heredoc'] = $r->toArray();

        $r = $dump->fetchTable('literalArrayliteral', array('name'));
        $report['array'] = $r->toArray();

        return $report;
    }

    public function hasResults(): bool {
        $dump = Dump::factory($this->config->dump);
        $r = $dump->fetchTable('literalString');

        return !empty($r);
    }
}

?>
