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

class SetMethodFnp extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
        // $x->foo();
        // $s::static_foo();
        $this->atomIs(array('Methodcall', 'Staticmethodcall'))
             ->hasNo('fullnspath')
             ->outIs(array('OBJECT', 'CLASS'))
             ->goToTypehint()
             ->savePropertyAs('fullnspath', 'fnp')
             ->back('first')

             ->outIs('METHOD')
             ->outIs('NAME')
             ->tokenIs('T_STRING')
             ->savePropertyAs('fullcode', 'name')
             ->back('first')

             ->raw('sideEffect{ it.get().property("fullnspath", fnp + "::" + name.toLowerCase()); }');
        $this->prepareQuery();
    }
}

?>
