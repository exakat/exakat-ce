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

class EnumCaseValues extends Analyzer {
    protected string $phpVersion = '8.1+';

    public function dependsOn(): array {
        return array('Complete/PropagateConstants',
                    );
    }

    public function analyze(): void {
        //E::Foo->value
        $this->atomIs('Member')
             ->hasNoOut('DEFINITION')
             ->outIs('MEMBER')
             ->as('value')
             ->fullcodeIs('value', self::CASE_INSENSITIVE)
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs('Staticconstant')
             ->inIs('DEFINITION')
             ->atomIs('Enumcase')
             ->outIs('VALUE')
             ->addEFrom('DEFINITION', 'value')
             ->back('first');
        $this->prepareQuery();

        //E::Foo->{value}
        $this->atomIs('Member')
             ->hasNoOut('DEFINITION')
             ->outIs('MEMBER')
             ->as('value')
             ->outIsIE('CODE') // Dynamic
             ->atomIs('String', self::WITH_CONSTANTS)
             ->noDelimiterIs('value', self::CASE_INSENSITIVE)
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs('Staticconstant')
             ->inIs('DEFINITION')
             ->outIs('VALUE')
             ->addEFrom('DEFINITION', 'value')
             ->back('first');
        $this->prepareQuery();

        //E::Foo->name
        $this->atomIs('Member')
             ->hasNoOut('DEFINITION')
             ->outIs('MEMBER')
             ->as('name')
             ->fullcodeIs('name', self::CASE_INSENSITIVE)
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs('Staticconstant')
             ->inIs('DEFINITION')
             ->outIs('NAME')
             ->addEFrom('DEFINITION', 'first')
             ->back('first');
        $this->prepareQuery();

        //E::Foo->{name}
        $this->atomIs('Member')
             ->hasNoOut('DEFINITION')
             ->outIs('MEMBER')
             ->as('name')
             ->outIsIE('CODE') // Dynamic
             ->atomIs('String', self::WITH_CONSTANTS)
             ->noDelimiterIs('name', self::CASE_INSENSITIVE)
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs('Staticconstant')
             ->inIs('DEFINITION')
             ->outIs('NAME')
             ->addEFrom('DEFINITION', 'first')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
