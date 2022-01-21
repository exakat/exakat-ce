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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;

class pregOptionE extends Analyzer {
    public const FETCH_DELIMITER = <<<'GREMLIN'
filter{ 
    base = it.get().value("noDelimiter").replaceAll("\\s", "");
    
    if (base.length() == 0) {
        false;
    } else {
        delimiter = base[0];
        if (delimiter == '\\\\') {
            false;
        } else {
            true;
        }
    }
}
GREMLIN;

    public const MAKE_DELIMITER_FINAL = <<<'GREMLIN'
sideEffect{ 
         if (delimiter == "{") { delimiter = "\\{";   delimiterFinal = "\\}"; } 
    else if (delimiter == "}") { delimiter = "\\}";   delimiterFinal = "\\}"; } 
    else if (delimiter == "(") { delimiter = "\\(";   delimiterFinal = "\\)"; } 
    else if (delimiter == ")") { delimiter = "\\)";   delimiterFinal = "\\)"; } 
    else if (delimiter == "[") { delimiter = "\\[";   delimiterFinal = "\\]"; } 
    else if (delimiter == "]") { delimiter = "\\]";   delimiterFinal = "\\]"; } 
    else if (delimiter == "*") { delimiter = "\\*";   delimiterFinal = "\\*"; } 
    else if (delimiter == "|") { delimiter = "\\|";   delimiterFinal = "\\|"; } 
    else if (delimiter == "?") { delimiter = "\\?";   delimiterFinal = "\\?"; } 
    else if (delimiter == "+") { delimiter = "\\+";   delimiterFinal = "\\+"; } 
    else if (delimiter == '$') { delimiter = "\\\$";  delimiterFinal = "\\\$"; } 
    else if (delimiter == ".") { delimiter = "\\.";   delimiterFinal = "\\." ; } 
    
    // default case : accept
    else { delimiterFinal = delimiter; } 
}
// Remove any invalid delimiter
.filter{ !(delimiter in ["\\", 
                         "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
                         "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
                         "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
                         ]); }

GREMLIN;

    public function analyze(): void {
        $functions = '\\preg_replace';

        // preg_match with a string
        $this->atomFunctionIs($functions)
             ->outWithRank('ARGUMENT', 0)
             ->tokenIs('T_CONSTANT_ENCAPSED_STRING')
             ->isNot('noDelimiter', '')
             ->raw(self::FETCH_DELIMITER)
             ->raw(self::MAKE_DELIMITER_FINAL)
             ->regexIs('noDelimiter', '^\\\\s*(" + delimiter + ").*(" + delimiterFinal + ")([a-df-zA-Z]*?e[a-df-zA-Z]*?)\$')
             ->back('first');
        $this->prepareQuery();

        // With an interpolated string "a $x b"
        $this->atomFunctionIs($functions)
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('String')
             ->outWithRank('CONCAT', 0)
             ->isNot('noDelimiter', '')
             ->raw(self::FETCH_DELIMITER)
             ->inIs('CONCAT')
             ->raw(self::MAKE_DELIMITER_FINAL)
             ->regexIs('fullcode', '^.\\\\s*(" + delimiter + ").*(" + delimiterFinal + ")([a-df-zA-Z]*?e[a-df-zA-Z]*?).\$')
             ->back('first');
        $this->prepareQuery();

        // with a concatenation
        $this->atomFunctionIs($functions)
             ->outWithRank('ARGUMENT', 0)
             ->atomIs('Concatenation')
             ->outWithRank('CONCAT', 0)
             ->atomIs('String')
             ->outIsIE('CONCAT')
             ->atomIs('String')
             ->is('rank', 0)
             ->isNot('noDelimiter', '')
             ->raw(self::FETCH_DELIMITER)
             ->inIsIE('CONCAT')
             ->raw(self::MAKE_DELIMITER_FINAL)
             ->regexIs('fullcode', '^.\\\\s*(" + delimiter + ").*(" + delimiterFinal + ")([a-df-zA-Z]*?e[a-df-zA-Z]*?).\$')
             ->back('first');
        $this->prepareQuery();
// Actual letters used for Options in PHP imsxeuADSUXJ (others may yield an error) case is important

        $this->atomFunctionIs(array('\\mb_eregi_replace',
                                    '\\mb_ereg_replace',
                                    ))
             ->outWithRank('ARGUMENT', 3)
             ->atomIs('String')
             ->tokenIs('T_CONSTANT_ENCAPSED_STRING')
             ->regexIs('noDelimiter', 'e')
             ->back('first');
         $this->prepareQuery();
    }
}

?>