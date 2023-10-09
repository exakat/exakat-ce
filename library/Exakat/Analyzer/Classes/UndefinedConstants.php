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

class UndefinedConstants extends Analyzer {
    public function dependsOn(): array {
        return array('Classes/DefinedConstants',
                    );
    }

    public function analyze(): void {
        // A::Undefined
        $this->atomIs('Staticconstant')
            // the name of the class is not known
             ->not(
                 $this->side()
                      ->outIs('CLASS')
                      ->atomIs(self::CONTAINERS)
                      ->goToTypehint()
                      ->atomIs('Void')
             )
             ->analyzerIsNot('Classes/DefinedConstants')
             ->isNot('isPhp', true)
             ->isNot('isExt', true)
             ->isNot('isStub', true)

             // skip arrayes like $a[1]::A
             ->outIs('CLASS')
             ->atomIsNot('Array')
             ->back('first')

             ->outIs('CONSTANT')
             ->atomIsNot('Block')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
