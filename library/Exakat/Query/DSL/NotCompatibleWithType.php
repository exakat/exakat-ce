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


namespace Exakat\Query\DSL;


class NotCompatibleWithType extends DSL {
    public const DISALLOW_NULL = false;
    public const ALLOW_NULL = true;

    public function run(): Command {
        switch (func_num_args()) {
            case 2 :
                list($types, $withNull) = func_get_args();
                $withNull = in_array($withNull, array(self::ALLOW_NULL, self::DISALLOW_NULL), STRICT_COMPARISON) ? $withNull : self::DISALLOW_NULL;
                break;

            case 1:
                list($types) = func_get_args();
                $withNull = self::DISALLOW_NULL;
                break;

            default:
                assert(func_num_args() <= 2, 'Wrong number of argument for ' . __METHOD__ . '. 2 are expected, ' . func_num_args() . ' provided');
        }

        if ($withNull === self::ALLOW_NULL) {
            $withNullGremlin = '.not(hasLabel("Null"))';
        } else {
            $withNullGremlin = '';
        }

        $query = <<<GREMLIN
where( 
__.sideEffect{ typehints = []; }
  .out("TYPEHINT", "RETURNTYPE")
  .has("fullnspath")
  $withNullGremlin
  .sideEffect{ typehints.add(it.get().value("fullnspath")) ; }
  .fold() 
)
.filter{
    results = true;
    typehinttype = it.get().value("typehint");

    for(typehint in typehints) {
        switch(typehint) {
            case "\\\\string":
                result = !($types in ["Magicconstant", "Heredoc", "String", "Concatenation", "Staticclass", "Shell"]);
                break;
                
            case "\\\\int":
                result = !($types in ["Integer", "Addition", "Multiplication", "Bitshift", "Logical", "Bitoperation", "Power", "Postplusplus", "Preplusplus", "Not"]);
                break;
    
            case "\\\\numeric":
                result = !($types in ["Integer", "Addition", "Multiplication", "Bitshift", "Logical", "Bitoperation", "Power", "Float", "Postplusplus", "Preplusplus"]);
                break;
    
            case "\\\\float":
                result = !($types in ["Float", "Addition", "Multiplication", "Bitshift", "Power"]);
                break;
    
            case "\\\\bool":
                result = !($types in ["Boolean", "Logical", "Not", "Comparison"]);
                break;
    
            case "\\\\array":
                result = !($types in ["Arrayliteral", "Addition"]);
                break;
    
            case "\\\\mixed":
                result = false; // anything is mixed, so this is always false
                break;

            case "\\\\null":
                result = !($types in ["Null"]);
                break;

            case "\\\\never":
                result = true; // never is compatible with everything
                break;

            case "\\\\false":
                result = !($types in ["Boolean"]) || (binding.hasVariable('fqn2') && fqn2 == "\\\\true"); 
                break;
    
            case "\\\\void":
            case "\\\\resource":
                result = false;
                break;

            // This is actually the case for all others types, so classes and interfaces
            // we may also end up with any new PHP native type there. 
            default:   
                result = !($types in ["New"]) || (fqn != typehint);
        }
        
        if (typehinttype == "one") {
            // straight from the comparison
            results = result;
        } else if (typehinttype == "or") {
            // One of them is sufficient 
            results = results && result;
        } else if (typehinttype == "and") {
            // this will not work with intersectional 
            results = results || result;
        } else {
            UnsupportedType();
        }
    }
    
    results;
}
GREMLIN;
        return new Command($query, array());
    }
}
?>
