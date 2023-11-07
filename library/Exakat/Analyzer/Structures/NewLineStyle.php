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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class NewLineStyle extends Analyzer {
    public function analyze(): void {
        $mapping = 'choose(__.hasLabel("String"), constant("slash-n"), constant("PHP_EOL"))';
        $storage = array('\\n'     => 'slash-n',
                         'PHP_EOL' => 'PHP_EOL');

        $this->atomIs(array('String', 'Identifier', 'Nsname'))
             ->raw('coalesce( __.hasLabel("Identifier", "Nsname").has("fullnspath").has("fullnspath", "\\\\PHP_EOL"), 
                              __.hasLabel("String").not(where(__.in("CONCAT").hasLabel("String", "Heredoc"))).has("noDelimiter", "\\\\n")
                             )')
             ->raw($mapping)
             ->groupCount();
        $types = $this->rawQuery()->toArray();

        if (empty($types)) {
            return;
        }
        $types = $types[0];

        $store = array();
        $total = 0;
        foreach ($storage as $key => $v) {
            $c = empty($types[$v]) ? 0 : $types[$v];
            $store[] = array('key'   => $key,
                             'value' => $c);
            $total += $c;
        }
        $this->datastore->addRowAnalyzer($this->analyzerQuoted, $store);

        if ($total == 0) {
            return;
        }

        $types = array_filter($types, function (int $x) use ($total): bool {
            return $x > 0 && $x  < 0.1 *  $total;
        });
        if (empty($types)) {
            return;
        }

        $this->atomIs(array('String', 'Identifier', 'Nsname'))
             ->raw('coalesce( __.hasLabel("Identifier", "Nsname").has("fullnspath").has("fullnspath", "\\\\PHP_EOL"), 
                              __.hasLabel("String").not(where(__.in("CONCAT").hasLabel("String", "Heredoc"))).has("noDelimiter", "\\\\n")
                             )')
             ->raw($mapping)
             ->isEqual(array_keys($types)[0])
             ->back('first');
        $this->prepareQuery();
    }
}

?>
