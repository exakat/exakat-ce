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


namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class DefinedConstants extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/IsStubStructure',
                     'Complete/IsPhpStructure',
                     'Complete/MakeClassConstantDefinition',
                     'Complete/OverwrittenConstants',
                     'Classes/IsExtClass',
                    );
    }

    public function analyze(): void {
        // constants defined at the class level
        // constants defined at the parents level
        // This includes interfaces
        $this->atomIs('Staticconstant')
             ->hasIn('DEFINITION');
        $this->prepareQuery();

        // constants defined in a class of an extension
        $this->atomIs('Staticconstant')
             ->outIs('CLASS')
             ->is('isPhp', true)
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('Staticconstant')
             ->outIs('CLASS')
             ->is('isStub', true)
             ->back('first');
        $this->prepareQuery();

        $this->atomIs('Staticconstant')
             ->outIs('CLASS')
             ->is('isExt', true)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
