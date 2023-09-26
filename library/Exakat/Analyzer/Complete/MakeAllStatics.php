<?php declare(strict_types = 1);
/*
 * Copyright 2012-2021 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


class MakeAllStatics extends Complete {
    public function analyze(): void {
        // static::X -> all children classes
        $this->atomIs('Static')
             ->inIs('CLASS')
             ->atomIs(array('Staticconstant', 'Staticmethodcall', 'Staticclass', 'Staticproperty'))
             ->back('first')

             ->inIs('DEFINITION')
             ->atomIs('Class')
             ->goToAllChildren(self::EXCLUDE_SELF)
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addEFrom('DEFINITION', 'first');
        $this->prepareQuery();

        // static::X -> all children classes via a trait
        $this->atomIs('Static')
             ->inIs('CLASS')
             ->atomIs(array('Staticconstant', 'Staticmethodcall', 'Staticclass', 'Staticproperty'))
             ->goToTrait()

             ->outIs('DEFINITION')
             ->inIs('USE') // This goes to all children classes too
             ->inIs('USE')

             ->atomIs(self::CLASSES_ALL)
             ->hasNoLinkYet('DEFINITION', 'first')
             ->addEFrom('DEFINITION', 'first');
        $this->prepareQuery();

        // static::X -> all children classes
        $this->atomIs('New')
             ->outIs('NEW')
             ->outIsIE('NAME')
             ->tokenIs('T_STATIC')
             ->as('results')
             ->goToClass()

             ->goToAllChildren(self::INCLUDE_SELF) // static are not link when in a new
             ->hasNoLinkYet('DEFINITION', 'results')
             ->addETo('DEFINITION', 'results');
        $this->prepareQuery();

        // static::X -> all children classes via a trait
        $this->atomIs('New')
             ->outIs('NEW')
             ->tokenIs('T_STATIC')
             ->as('results')
             ->goToTrait()

             ->outIs('DEFINITION')
             ->inIs('USE')
             ->inIs('USE')

             ->atomIs(self::CLASSES_ALL)

             ->hasNoLinkYet('DEFINITION', 'results')
             ->addEFrom('DEFINITION', 'results');
        $this->prepareQuery();
    }
}

?>
