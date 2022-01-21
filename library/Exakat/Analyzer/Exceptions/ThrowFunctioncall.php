<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

namespace Exakat\Analyzer\Exceptions;

use Exakat\Analyzer\Analyzer;

class ThrowFunctioncall extends Analyzer {
    public function analyze(): void {
        $phpClasses    = $this->loadIni('php_classes.ini', 'classes');
        $phpClassesFnp = makeFullNsPath($phpClasses);

        // throw className(), defined class, no function
        $this->atomIs('Throw')
             ->outIs('THROW')
             ->tokenIs(array_merge(self::STATICCALL_TOKEN, array('T_OBJECT_OPERATOR', 'T_DOUBLE_COLON', 'T_NULLSAFE_OBJECT_OPERATOR', 'T_OPEN_BRACKET')))
             ->atomIs(array('Functioncall', 'Staticmethodcall', 'Methodcall'))
             ->hasNoFunctionDefinition()
             ->back('first');
        $this->prepareQuery();

        // todo : consider typehint : a functioncall may return an exception, a property may be typed Exception.

        // throw RuntimeException()
        $this->atomIs('Throw')
             ->outIs('THROW')
             ->tokenIs(self::STATICCALL_TOKEN)
             ->atomIsNot(array('Array', 'Functioncall'))
             ->fullnspathIs($phpClassesFnp)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
