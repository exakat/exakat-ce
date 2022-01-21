<?php
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

namespace Exakat\Analyzer\Functions;

use Exakat\Analyzer\Analyzer;

class DeprecatedCallable extends Analyzer {
    protected $phpVersion = '8.2-';

    public function dependsOn(): array {
        return array('Complete/PropagateConstants',
                    );
    }

    public function analyze() : void {
        /*
"self::method"
"parent::method"
"static::method"
        */
        $this->atomIs('String')
             ->regexIs('noDelimiter', '(?i)^(self|parent|static)::') //
             ->back('first');
        $this->prepareQuery();

        /*
["self", "method"]
["parent", "method"]
["static", "method"]
        */
        $this->atomIs('Arrayliteral')
             ->is('count', 2)
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->codeIs(array('self', 'parent', 'static'), self::TRANSLATE, self::CASE_INSENSITIVE)
             ->back('first')
             
             ->outWithRank('ARGUMENT', 1)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->regexIsNot('noDelimiter', '::')

             ->back('first');
        $this->prepareQuery();

/*
["Foo", "Bar::method"]
*/
        $this->atomIs('Arrayliteral')
             ->is('count', 2)
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->back('first')
             
             ->outWithRank('ARGUMENT', 1)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->regexIs('noDelimiter', '::')

             ->back('first');
        $this->prepareQuery();

/*
[new Foo, "Bar::method"]
*/
        $this->atomIs('Arrayliteral')
             ->is('count', 2)
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('New', self::WITH_VARIABLES)
             ->back('first')
             
             ->outWithRank('ARGUMENT', 1)
             ->atomIs('String', self::WITH_CONSTANTS)
             ->regexIs('noDelimiter', '::')

             ->back('first');
        $this->prepareQuery();


    }
}

?>
