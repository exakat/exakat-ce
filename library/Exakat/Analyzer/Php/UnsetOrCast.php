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

namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;

class UnsetOrCast extends Analyzer {
    protected string $phpVersion = '8.0-';

    public function analyze(): void {
        $mapping = <<<'GREMLIN'
choose(__.hasLabel("Cast"), constant("(unset)"), constant("unset( )"))
GREMLIN;
        $storage = array('(unset)'  => '(unset)',
                         'unset( )' => 'unset( )');

        $this->atomIs(array('Unset', 'Cast'))
             ->raw('or( hasLabel("Cast").has("token", "T_UNSET_CAST"), hasLabel("Unset"))')
             ->raw($mapping) // @todo : make this a step!
             ->raw('groupCount("gf").cap("gf")');
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
            return $x > 0 && $x / $total < 0.1;
        });
        if (empty($types)) {
            return;
        }

        $this->atomIs(array('Unset', 'Cast'))
             ->raw('or( hasLabel("Cast").has("token", "T_UNSET_CAST") , hasLabel("Unset"))')
             ->raw($mapping) // @todo : make this a step!
             ->isEqual(array_keys($types)[0]) // Only one value in the array at any time
             ->back('first');
        $this->prepareQuery();
    }
}

?>