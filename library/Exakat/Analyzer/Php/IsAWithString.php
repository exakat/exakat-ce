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
use Exakat\Query\DSL\FollowParAs;

class IsAWithString extends Analyzer {
    public function dependsOn(): array {
        return array('Variables/IsLocalConstant',
                     'Complete/PropagateCalls',
                    );
    }

    public function analyze(): void {
        $functions = array('\\is_a',
                           '\\is_subclass_of',
                          );
        // is_a('a', 'b');
        $this->atomFunctionIs($functions)
             ->noChildWithRank('ARGUMENT', 2)
             ->outWithRank('ARGUMENT', 0)
             ->followParAs(FollowParAs::FOLLOW_PARAS_TERNARY)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->back('first');
        $this->prepareQuery();

        // @todo : check for the third argument's value to be false

        // is_a('a', 'b', false);
        $this->atomFunctionIs($functions)
             ->outWithRank('ARGUMENT', 2)
             ->is('boolean', false)
             ->back('first')
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
