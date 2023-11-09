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

namespace Exakat\Analyzer\Complete;

class CreateForeachDefault extends Complete {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                    );
    }

    public function analyze(): void {
        // const A = [1 => 2]; foreach(A as $v) {}
        $this->atomIs('Foreach')
             ->outIs('VALUE')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->as('v')
             ->back('first')

             ->outIs('SOURCE')
             ->atomIsNot('Variable')
             ->atomIs('Arrayliteral', self::WITH_CONSTANTS)
             ->outIs('ARGUMENT')
             ->outIsIE('VALUE')
             // avoid doubles
             ->hasNoLinkYet('DEFAULT', 'v')
             ->addEFrom('DEFAULT', 'v');
        $this->prepareQuery();

        // $a = [1 => 2]; foreach($a as $v) {}
        $this->atomIs('Foreach')
             ->outIs('VALUE')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->as('v')
             ->back('first')

             ->outIs('SOURCE')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->outIs('DEFAULT')
             ->atomIs('Arrayliteral')
             ->outIs('ARGUMENT')
             ->outIsIE('VALUE')
             // avoid doubles
             ->hasNoLinkYet('DEFAULT', 'v')
             ->addEFrom('DEFAULT', 'v');
        $this->prepareQuery();

        // $a = [1 => 2]; foreach($a as $k => $v) {}
        $this->atomIs('Foreach')
             ->outIs('INDEX')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->as('v')
             ->back('first')

             ->outIs('SOURCE')
             ->atomIsNot('Variable')
             ->atomIs('Arrayliteral', self::WITH_CONSTANTS)
             ->outIs('ARGUMENT')
             ->outIs('INDEX')
             // avoid doubles
             ->hasNoLinkYet('DEFAULT', 'v')
             ->addEFrom('DEFAULT', 'v');
        $this->prepareQuery();

        // $a = [1 => 2]; foreach($a as $k => $v) {}
        $this->atomIs('Foreach')
             ->outIs('INDEX')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->as('v')
             ->back('first')

             ->outIs('SOURCE')
             ->atomIs('Variable')
             ->inIs('DEFINITION')
             ->outIs('DEFAULT')
             ->outIs('ARGUMENT')
             ->outIs('INDEX')
             // avoid doubles
             ->hasNoLinkYet('DEFAULT', 'v')
             ->addEFrom('DEFAULT', 'v');
        $this->prepareQuery();

        // @todo : with list $a = [1 => [2, 3]]; foreach($a as $k => [$v1, $v2]) {}
        // @todo : this is getting messup when multiple loops that have the same blind variables
    }
}

?>
