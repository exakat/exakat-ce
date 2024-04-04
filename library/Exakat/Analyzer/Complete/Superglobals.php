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

namespace Exakat\Analyzer\Complete;

use Exakat\Analyzer\Analyzer;

class Superglobals extends Analyzer {
    public function analyze(): void {
        $this->atomIs('Phpvariable')
             ->values(array('fullcode', 'code'))
             ->unique();
        $res = $this->rawQuery()->toArray();

        if (empty($res)) {
            return;
        }

        foreach ($res as $row) {
            list($fullcode, $code) = array_values($row[0]);
            $result = $this->query('g.V().hasLabel("Globaldefinition").has("code", ' . $code . ').count()');

            // This prevent duplicates of that node
            if ($result->toInt() === 1) {
                continue;
            }

            $fullcode = str_replace('&', '', $fullcode);
            $fullcode = str_replace('$', '\\$', $fullcode);

            $result2 = $this->query('g.addV("Globaldefinition")
			.property("fullcode", "' . $fullcode . '")
			.property("code", "' . $code . '")
			.property("line", 0)
			.property("extra", true)
			.id()');

            $this->atomIs('Phpvariable')
                 ->codeIs($code, self::NO_TRANSLATE, self::CASE_SENSITIVE)
                 ->addEFrom('DEFINITION', $result2->toInt());
            $this->rawQuery();
        }
    }
}

?>
