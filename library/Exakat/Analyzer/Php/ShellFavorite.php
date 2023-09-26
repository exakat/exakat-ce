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

class ShellFavorite extends Analyzer {
    public function analyze(): void {
        $mapping = <<<'GREMLIN'
choose( __.hasLabel("Shell"), constant("`backtick`"), 
	choose( __.has("fullnspath", "\\exec"), constant("exec"), 
		choose( __.has("fullnspath", "\\shell_exec"), constant("shell_exec"), 
			constant("none")
		)
	)
)
GREMLIN;
        $storage = array('shell_exec' => 'shell_exec',
                         'exec'       => 'exec',
                         '`backtick`' => '`backtick`',
                         'none'		  => 'none',
                         );

        $this->atomIs(array('Functioncall', 'Shell'))
             ->raw('or( hasLabel("Shell"), has("fullnspath", within("\\\\exec", "\\\\shell_exec")))')
             ->raw($mapping)
             ->raw('groupCount("gf").cap("gf").sideEffect{ s = it.get().values().sum(); }');
        $types = $this->rawQuery()->toArray();

        if (empty($types)) {
            return;
        }
        $types = $types[0];
        unset($types['none']);

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

        $this->atomIs(array('Functioncall', 'Shell'))
             ->raw('or( hasLabel("Shell"), has("fullnspath", within("\\\\exec", "\\\\shell_exec")))')
             ->raw($mapping)
             ->raw('where(is(within(***)))', array_keys($types))
             ->back('first');
        $this->prepareQuery();
    }
}

?>