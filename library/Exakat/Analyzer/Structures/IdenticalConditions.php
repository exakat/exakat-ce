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

class IdenticalConditions extends Analyzer {
    public function analyze(): void {
        // $a || $a
        // ($a) && ($a)
        $this->atomIs(self::LOGICAL_ALL)
             ->analyzerIsNot('self')
             ->hasNoIn(array('LEFT', 'RIGHT'))
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->atomIsNot(self::LOGICAL_ALL)
             ->savePropertyAs('fullcode', 'left')
             ->inIsIE('CODE')
             ->inIs('RIGHT')
             ->outIs('LEFT')
             ->outIsIE('CODE')
             ->atomIsNot(self::LOGICAL_ALL)
             ->samePropertyAs('fullcode', 'left', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // $a || $a || $a
        // ($a) && ($a)
        // two levels
        $this->atomIs(self::LOGICAL_ALL)
             ->analyzerIsNot('self')
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->savePropertyAs('fullcode', 'right')
             ->inIsIE('CODE')
             ->inIs('RIGHT')
             ->outIs('LEFT')
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->outIsIE('CODE')
             ->samePropertyAs('fullcode', 'right', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        $this->atomIs(self::LOGICAL_ALL)
             ->analyzerIsNot('self')
             ->outIs('LEFT')
             ->outIsIE('CODE')
             ->savePropertyAs('fullcode', 'left')
             ->inIsIE('CODE')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->outIsIE('CODE')
             ->samePropertyAs('fullcode', 'left', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // case for $a || $b || $b
        $this->atomIs(self::LOGICAL_ALL)
             ->analyzerIsNot('self')
             // Ignore LEFT
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)

             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->savePropertyAs('fullcode', 'right')
             ->inIsIE('CODE')
             ->inIs('RIGHT')

             ->outIs('LEFT')
             ->outIsIE('CODE')
             ->samePropertyAs('fullcode', 'right', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // $a || $a || $a
        // ($a) && ($a)
        // three levels
        // straight structure
        $this->atomIs(self::LOGICAL_ALL)
             ->analyzerIsNot('self')
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->savePropertyAs('fullcode', 'left')
             ->inIsIE('CODE')
             ->inIs('RIGHT')
             ->outIs('LEFT')
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->samePropertyAs('fullcode', 'left', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // reverse structure
        $this->atomIs(self::LOGICAL_ALL)
             ->analyzerIsNot('self')
             ->outIs('LEFT')
             ->outIsIE('CODE')
             ->savePropertyAs('fullcode', 'left')
             ->inIsIE('CODE')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->samePropertyAs('fullcode', 'left', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // $a || $a || $a
        // ($a) && ($a)
        // four levels
        $this->atomIs(self::LOGICAL_ALL)
             ->analyzerIsNot('self')
             ->outIs('LEFT')
             ->outIsIE('CODE')
             ->savePropertyAs('fullcode', 'left')
             ->inIsIE('CODE')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->samePropertyAs('fullcode', 'left', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // $a || $a || $a
        // ($a) && ($a)
        // four levels
        $this->atomIs(self::LOGICAL_ALL)
             ->analyzerIsNot('self')
             ->outIs('LEFT')
             ->outIsIE('CODE')
             ->savePropertyAs('fullcode', 'left')
             ->inIsIE('CODE')
             ->inIs('LEFT')
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->outIsIE('CODE')
             ->atomIs(self::LOGICAL_ALL)
             ->outIs(array('RIGHT', 'LEFT'))
             ->samePropertyAs('fullcode', 'left', self::CASE_SENSITIVE)
             ->back('first');
        $this->prepareQuery();

        // @todo : also adding situations like ($a and !$a) ?
    }
}

?>
