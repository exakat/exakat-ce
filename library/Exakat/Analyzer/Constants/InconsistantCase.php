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


namespace Exakat\Analyzer\Constants;

use Exakat\Analyzer\Analyzer;

class InconsistantCase extends Analyzer {
    public function analyze(): void {
        $lower = $this->dictCode->translate(array('true', 'false', 'null'));
        $upper = $this->dictCode->translate(array('TRUE', 'FALSE', 'NULL'));

        if (empty($lower) && empty($upper)) {
            return;
        }

        $mapping = <<<'GREMLIN'
if (it.get().value('code').toLong() in ***) { 
    x2 = 'lower'; 
} else if (it.get().value('code').toLong() in ***) { 
    x2 = 'upper'; 
} else {
    x2 = 'mixed'; 
}
GREMLIN;
        $storage = array('lowercase' => 'lower',
                         'UPPERCASE' => 'upper',
                         'Mixed'     => 'mixed');


        $this->atomIs(array('Null', 'Boolean'))
             ->raw('map{ ' . $mapping . ' }', $lower, $upper)
             ->raw('groupCount("gf").cap("gf").sideEffect{ s = it.get().values().sum(); }');
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

        if ($total === 0) {
            return;
        }

        $types = array_filter($types, function (int $x) use ($total): bool {
            return $x > 0 && $x / $total < 0.1;
        });
        $types = '[' . str_replace('\\', '\\\\', makeList(array_keys($types))) . ']';

        $this->atomIs(array('Null', 'Boolean'))
             ->raw('map{ ' . $mapping . ' }', $lower, $upper)
             ->raw('filter{ x2 in ' . $types . '}')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
