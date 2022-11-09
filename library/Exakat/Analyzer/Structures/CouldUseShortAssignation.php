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
use Exakat\Query\DSL\FollowParAs;

class CouldUseShortAssignation extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // Commutative operation : Addition
        // $a = $a + 1 (no array)
        $this->atomIs('Assignation')
             ->codeIs('=')
             ->outIs('LEFT')
             ->savePropertyAs('fullcode', 'receiver')
             ->not(
                 $this->side()
                      ->goToTypehint()
                      ->fullnspathIs('\\array')
             )
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->followParAs(FollowParAs::FOLLOW_PARAS_ONLY)
             ->tokenIs('T_PLUS')
             ->hasNoChildren('Arrayliteral', array( array('LEFT', 'RIGHT')) )
             ->outIsIE(array('LEFT', 'RIGHT', 'CODE'))
             ->hasNoParent('Coalesce', array('LEFT'))
             ->samePropertyAs('fullcode', 'receiver', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // Commutative operation : Multiplication
        // $a = $a * 1
        $this->atomIs('Assignation')
             ->codeIs('=')
             ->outIs('LEFT')
             ->savePropertyAs('fullcode', 'receiver')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->atomInsideExpression('Multiplication')
             ->tokenIs('T_STAR')
             ->outIsIE(array('LEFT', 'RIGHT', 'CODE'))
             ->samePropertyAs('fullcode', 'receiver', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // Non-Commutative operation
        // $a = $a - 1;
        $this->atomIs('Assignation')
             ->codeIs('=')
             ->outIs('LEFT')
             ->savePropertyAs('fullcode', 'receiver')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->outIsIE('CODE') // skip ()
             ->codeIs(array('-', '/', '%', '<<', '>>', '**', '&', '^', '|', '??'))
             ->outIs('LEFT')
             ->outIsIE('CODE') // skip ()
             ->samePropertyAs('fullcode', 'receiver', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // Special case for .
        // $a = $a . 1;
        $this->atomIs('Assignation')
             ->codeIs('=')
             ->outIs('LEFT')
             ->savePropertyAs('fullcode', 'receiver')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->atomIs('Concatenation')
             ->outWithRank('CONCAT', 0)
             ->samePropertyAs('fullcode', 'receiver', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
