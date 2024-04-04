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

namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;

class GlobalsVsGlobal extends Analyzer {
    public function analyze(): void {
        $globals = $this->dictCode->translate(array('$GLOBALS'));

        if (empty($globals)) {
            return;
        }

        $globals = (int) $globals[0];

        $mapping = <<<GREMLIN
choose( __.has("code", $globals), 
	constant("GLOBALS"),
	constant("global")
)
GREMLIN;
        $storage = array('$GLOBALS' => 'GLOBALS',
                         'global'   => 'global');

        $this->atomIs(self::VARIABLES_ALL)
             ->raw('or( has("code", ' . $globals . '), __.in("GLOBAL")) ')
             ->raw($mapping)
             ->groupCount();
        $types = $this->rawQuery()->toArray()[0];

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
        if (empty($types)) {
            return;
        }

        $this->atomIs(self::VARIABLES_ALL)
             ->raw('or( has("code", ' . $globals . '), __.in("GLOBAL")) ')
             ->raw($mapping)
             ->isEqual(array_keys($types))
             ->back('first');
        $this->prepareQuery();
    }
}

?>
