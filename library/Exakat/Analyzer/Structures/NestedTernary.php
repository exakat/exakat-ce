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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;
use Exakat\Query\DSL\FollowParAs;

class NestedTernary extends Analyzer {
    protected int $minNestedTernary = 2;

    public function analyze(): void {
        if ($this->minNestedTernary < 2) {
            display('minNestedTernary is too low. It must be it 2 or more. Omitting.');

            return;
        }
        //$a ? $b : $c ? $d : $e
        $this->atomIs('Ternary');

        for ($i = 0; $i < $this->minNestedTernary - 1; ++$i) {
            $this->outIs(array('THEN', 'ELSE'))
                 ->followParAs(FollowParAs::FOLLOW_PARAS_ONLY) // for parenthesis and assignations
                 ->atomIs('Ternary');
        }
        $this->back('first');

        $this->prepareQuery();
    }
}

?>
