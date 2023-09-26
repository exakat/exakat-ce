<?php
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
declare(strict_types = 1);

namespace Exakat\Analyzer\Complete;

use Exakat\Query\DSL\SavePropertyAs;

class PropagateConstants extends Complete {
    private const MAX_RECURSION = 15;

    public function analyze(): void {
        $this->setDefineConstantAside();
        $this->readConstantValue();

        $this->pushConstantValues();
        $count = $this->propagate();

        $this->setCount($count);
    }

    private function setDefineConstantAside(): void {
        $this->atomIs('Defineconstant')
             ->outIs('NAME')
             ->setProperty('propagated', true);
        $res = $this->rawQuery();

        display( $res->toInt() . " constants skipped\n");
    }

    private function propagate(int $level = 0): int {
        $total = 0;

        //Currently handles + - * / % . << >> ** ()
        //Currently handles intval, boolean, noDelimiter (String)
        //Needs realval, arrayval

        // @todo : handle casts

        $total += $this->processAddition();
        $total += $this->processConcatenation();
        $total += $this->processSign();
        $total += $this->processPower();
        $total += $this->processComparison();
        $total += $this->processLogical();
        $total += $this->processParenthesis();
        $total += $this->processNot();
        $total += $this->processCoalesce();
        $total += $this->processTernary();
        $total += $this->processBitshift();
        $total += $this->processMultiplication();
        $this->readConstantValue();
        $this->pushConstantValues();

        if ($total > 0 && $level < self::MAX_RECURSION) {
            $total += $this->propagate($level + 1);
        }

        return $total;
    }

    private function readConstantValue(): int {
        display('propagating Constant value in Const');
        // fix path for constants with Const
        // noDelimiter is set at the same moment as boolean and intval. Any of them is the same
        $this->atomIs(array('Constant', 'Defineconstant'))
         ->outIs('VALUE')
         ->atomIs(array('String', 'Heredoc', 'Integer', 'Null', 'Boolean', 'Float'))
         ->setProperty('propagated', true)
         ->count();
        $res = $this->rawQuery();

        $this->atomIs(array('Constant', 'Defineconstant'))
             ->outIs('VALUE')
             ->is('propagated', true)
             ->savePropertyAs('x')
             ->back('first')

             ->outIs('NAME')
             ->hasNo('propagated')
             ->raw(<<<'GREMLIN'
 sideEffect{ 
        if ("noDelimiter" in x.keys()) {
            it.get().property("noDelimiter", x.value("noDelimiter").toString()); 
        }
        if ("intval" in x.keys()) {
            it.get().property("intval", x.value("intval")); 
        }
        if ("boolean" in x.keys()) {
            it.get().property("boolean", x.value("boolean")); 
        }
        if ("isNull" in x.keys()) {
            it.get().property("isNull", x.value("isNull")); 
        }
        if ("count" in x.keys()) {
            it.get().property("count", x.value("count")); 
        }
        it.get().property("propagated", true); 
}
GREMLIN
             )
                 ->count();
        $res = $this->rawQuery();

        display( $res->toInt() . " constants inited\n");
        return $res->toInt();
    }

    private function pushConstantValues(): int {
        $this->atomIs(array('Constant', 'Defineconstant'))
             ->outIs('VALUE')
             ->is('propagated', true)
             ->savePropertyAs('constante')
             ->back('first')

             ->outIs('DEFINITION')
             ->hasNo('propagated')
             ->raw(<<<'GREMLIN'
sideEffect{ 
        if ("intval" in constante.keys()) {
            it.get().property("intval", constante.value("intval")); 
        }
        if ("boolean" in constante.keys()) {
            it.get().property("boolean", constante.value("boolean")); 
        }
        if ("noDelimiter" in constante.keys()) {
            it.get().property("noDelimiter", constante.value("noDelimiter").toString()); 
        }
        if ("isNull" in constante.keys()) {
            it.get().property("isNull", constante.value("isNull")); 
        }
        it.get().property("propagated", true); 
}
GREMLIN
             )
             ->count();
        $res = $this->rawQuery();

        display( $res->toInt() . " constants propagated\n");
        return $res->toInt();
    }

    private function processAddition(): int {
        display('propagating Constant value in Addition');
        // fix path for constants with Const
        $this->atomIs('Addition')
             ->hasNo('propagated')
             // Split LEFT and RIGHT to ensure left is in 0
             ->filter(
                 $this->side()
                      ->outIs('LEFT')
                      ->savePropertyAs('intval', 'left')
             )
             ->filter(
                 $this->side()
                      ->outIs('RIGHT')
                      ->savePropertyAs('intval', 'right')
             )

            ->raw(<<<'GREMLIN'
sideEffect{ 
    if (it.get().value("token") == 'T_PLUS') {
      i = Long.valueOf(left) + Long.valueOf(right);
    } else if (it.get().value("token") == 'T_MINUS') {
      i = Long.valueOf(left) - Long.valueOf(right);
    }

    it.get().property("intval", i.toLong()); 
    it.get().property("boolean", i != 0);
    it.get().property("noDelimiter", i.toString()); 
    it.get().property("propagated", true); 
    
    i = null;
}

GREMLIN
            )
             ->count();

        $res = $this->rawQuery();
        display('propagating ' . $res->toInt() . ' Addition with constants');

        return $res->toInt();
    }

    private function processConcatenation(): int {
        display('propagating Constant value in Concatenations');
        $this->atomIs('Concatenation')
             ->hasNo('propagated')
             ->initVariable('x', '[ ]')
             ->not(
                 $this->side()
                      ->outIs('CONCAT')
                      ->hasNo('noDelimiter')
             )
             ->not(
                 $this->side()
                      ->outIs('CONCAT')
                      ->atomIs(array('Identifier', 'Nsname'))
                      ->hasNo('propagated')
             )
             ->filter(
                 $this->side()
                      ->outIs('CONCAT')
                      ->raw('order().by("rank").sideEffect{ x.add( it.get().value("noDelimiter") ) }.fold()')
             )
             ->raw(<<<'GREMLIN'
sideEffect{ 
    s = x.join("");
    it.get().property("noDelimiter", s);

    // Warning : PHP doesn't handle error that same way
    if (s.isInteger()) {
        it.get().property("intval", s.toInteger());
        it.get().property("boolean", true);
    } else {
        it.get().property("intval", 0);
        it.get().property("boolean", false);
    }
    it.get().property("propagated", true); 
    
    x = null;
}

GREMLIN
             )
        ->count();

        $res = $this->rawQuery();
        display('propagating ' . $res->toInt() . ' Concatenation with constants');

        return $res->toInt();
    }

    private function processSign(): int {
        display('propagating Constant value in Sign');
        $this->atomIs('Sign')
             ->hasNo('propagated')
             ->outIs('SIGN')
             ->savePropertyAs('intval', 'x')
             ->inIs('SIGN')
             ->raw(<<<'GREMLIN'
sideEffect{ 
        if (it.get().value("token") == 'T_PLUS') {
            it.get().property("intval", x); 
            it.get().property("boolean", x != 0);
            it.get().property("noDelimiter", x.toString()); 
        } else if (it.get().value("token") == 'T_MINUS') {
            it.get().property("intval", -1 * x.toInteger()); 
            it.get().property("boolean", x != 0);
            it.get().property("noDelimiter", (-1 * x.toInteger()).toString()); 
        }
        it.get().property("propagated", true); 

        i = null;
}
GREMLIN
             )
           ->count();
        $res = $this->rawQuery();

        display('propagating ' . $res->toInt() . ' Signs with constants');
        return $res->toInt();
    }

    private function processPower(): int {
        display('propagating Constant value in Power');

        // fix path for constants with Const
        $this->atomIs('Power')
             ->hasNo('propagated')
             // Split LEFT and RIGHT to ensure left is in 0
             ->filter(
                 $this->side()
                      ->outIs('LEFT')
                      ->has('intval')
                      ->savePropertyAs('noDelimiter', 'left')
             )
             ->filter(
                 $this->side()
                      ->outIs('RIGHT')
                      ->has('intval')
                      ->savePropertyAs('noDelimiter', 'right')
             )

            ->raw(<<<'GREMLIN'
 filter{ left.isNumber() && right.isNumber(); }.
sideEffect{ 
    // Using BigInteger was failing with powwer call : the query was stuck until it dies.
    i = new BigDecimal(left);
    i = i.power(Float.valueOf(right));

    if (i > (new BigInteger(2)).pow(63)) {
        i = 0;
    }

    it.get().property("intval", i.toLong()); 
    // Note : float are not supported, so this will be rounded
    it.get().property("boolean", i.toLong() != 0);
    it.get().property("noDelimiter", i.toString()); 
    it.get().property("propagated", true); 

    i = null;
}

GREMLIN
            )
            ->count();

        $res = $this->rawQuery();
        display('propagating ' . $res->toInt() . ' power with constants');

        return $res->toInt();
    }

    private function processComparison(): int {
        display('propagating Constant value in Comparison');
        // fix path for constants with Const
        $this->atomIs('Comparison')
                 ->hasNo('propagated')
                 // Split LEFT and RIGHT to ensure left is in 0
                 ->filter(
                     $this->side()
                          ->outIs('LEFT')
                          ->savePropertyAs('intval', 'left')
                 )
                 ->filter(
                     $this->side()
                          ->outIs('RIGHT')
                          ->savePropertyAs('intval', 'right')
                 )

             ->raw(<<<'GREMLIN'
sideEffect{ 
        if (it.get().value("token") == 'T_GREATER') {
          i = left > right;
        } else if (it.get().value("token") == 'T_SMALLER') {
          i = left < right;
        } else if (it.get().value("token") == 'T_IS_GREATER_OR_EQUAL') {
          i = left >= right;
        } else if (it.get().value("token") == 'T_IS_SMALLER_OR_EQUAL') {
          i = left <= right;
        } else if (it.get().value("token") == 'T_IS_EQUAL' ||
                   it.get().value("token") == 'T_IS_IDENTICAL') {
          i = left == right;
        } else if (it.get().value("token") == 'T_IS_NOT_EQUAL'||
                   it.get().value("token") == 'T_IS_NOT_IDENTICAL') {
          i = left != right;
        }

    it.get().property("intval", i ? 1 : 0); 
    it.get().property("boolean", i != 0);
    it.get().property("noDelimiter", i ? '1' : ''); 
    it.get().property("propagated", true); 

    i = null;
}

GREMLIN
             )
            ->count();

        $res = $this->rawQuery();
        display('propagating ' . $res->toInt() . ' comparison with constants');

        return $res->toInt();
    }

    private function processLogical(): int {
        display('propagating Constant value in Logical');
        // fix path for constants with Const
        $this->atomIs(self::LOGICAL_ALL)
                 ->hasNo('propagated')
                 // Split LEFT and RIGHT to ensure left is in 0
                 ->filter(
                     $this->side()
                          ->outIs('LEFT')
                          ->savePropertyAs('intval', 'left')
                 )
                 ->filter(
                     $this->side()
                          ->outIs('RIGHT')
                          ->savePropertyAs('intval', 'right')
                 )

             ->raw(<<<'GREMLIN'
sideEffect{ 
      if (it.get().value("token") == 'T_BOOLEAN_AND' ||
          it.get().value("token") == 'T_LOGICAL_AND') {
        i = (left != 0) && (right != 0);
        s = i ? '1' : '0';
      } else if (it.get().value("token") == 'T_BOOLEAN_OR' ||
                 it.get().value("token") == 'T_LOGICAL_OR') {
        i = (left != 0) || (right != 0);
        s = i ? '1' : '0';
      } else if (it.get().value("token") == 'T_LOGICAL_XOR') {
        i = (left != 0) ^ (right != 0);
        s = i ? '1' : '0';
      } else if (it.get().value("token") == 'T_AND' ||
                 it.get().value("token") == 'T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG') {
        i = left.toLong() & right.toLong();
        s = i.toString();
      } else if (it.get().value("token") == 'T_XOR') {
        i = left.toLong() ^ right.toLong();
        s = i.toString();
      } else if (it.get().value("token") == 'T_OR') {
        i = left.toLong() | right.toLong();
        s = i.toString();
      } else if (it.get().value("token") == 'T_SPACESHIP') {
        i = left <=> right;
        if (i == 1) {
           s = '1';
        } else if (i == 0) {
           s = '0';
        } else {
           s = '-1';
        }
      } else {
        Missing_logical_case_in_constant_propagation();
      }

    it.get().property("intval", i ? 1 : 0); 
    it.get().property("boolean", i != 0);
    it.get().property("noDelimiter", s); 
    it.get().property("propagated", true); 

}
GREMLIN
             )
            ->count();

        $res = $this->rawQuery();
        display('propagating ' . $res->toInt() . ' logical with constants');

        return $res->toInt();
    }

    private function processParenthesis(): int {
        display('propagating Constant value in Parenthesis');
        $this->atomIs('Parenthesis')
             ->hasNo('propagated')
             ->filter(
                 $this->side()
                      ->outIs('CODE')
                      ->has('intval')
                      ->savePropertyAs('parenthesis')
             )
             ->raw(<<<'GREMLIN'
sideEffect{ 
    it.get().property("intval", parenthesis.value("intval")); 
    if (it.get().property("boolean") != null) {
        it.get().property("boolean", parenthesis.value("boolean"));
    }
    if ("noDelimiter" in parenthesis.keys()) {
        // Ternary, Comparison
        it.get().property("noDelimiter", parenthesis.value("noDelimiter").toString()); 
    }
    it.get().property("propagated", true); 

    parenthesis = null;
}
GREMLIN
             )
           ->count();
        $res = $this->rawQuery();

        display('propagating ' . $res->toInt() . ' Parenthesis with constants');
        return $res->toInt();
    }

    private function processNot(): int {
        display('propagating Constant value in Not');
        $this->atomIs('Not')
             ->hasNo('propagated')
             ->filter(
                 $this->side()
                      ->outIs('NOT')
                      ->has('intval')
                      ->has('noDelimiter')
                      ->savePropertyAs(savePropertyAs::ATOM, 'x')
             )
             ->raw(<<<'GREMLIN'
sideEffect{ 
    if (it.get().value("token") == 'T_BANG') {
      i = !x.value("intval");
    } else if (it.get().value("token") == 'T_TILDE') { 
      i = ~x.value("intval");
    }

    it.get().property("intval", i ? 1 : 0); 
    if (it.get().property("boolean") != null) {
        it.get().property("boolean", !x.value("boolean"));
    }
    it.get().property("noDelimiter", x.value("noDelimiter").toString()); 
    it.get().property("propagated", true); 

    x = null;
}
GREMLIN
             )
           ->count();
        $res = $this->rawQuery();

        display('propagating ' . $res->toInt() . ' Not with constants');
        return $res->toInt();
    }

    private function processCoalesce(): int {
        display('propagating Constant value in Coalesce');
        $this->atomIs('Coalesce')
             ->hasNo('propagated')
             // Split LEFT and RIGHT to ensure left is in 0
             ->filter(
                 $this->side()
                      ->outIs('LEFT')
                      ->savePropertyAs('intval', 'left')
             )
             ->filter(
                 $this->side()
                      ->outIs('RIGHT')
                      ->savePropertyAs('intval', 'right')
             )
             ->raw(<<<'GREMLIN'
sideEffect{ 
    if (left == 0) {
      i = right;
    } else {
      i = left;
    }
    
    it.get().property("intval", i); 
    it.get().property("boolean", i != 0);
    it.get().property("noDelimiter", i.toString()); 
    it.get().property("propagated", true); 

    i = null;
}
GREMLIN
             )
           ->count();
        $res = $this->rawQuery();

        display('propagating ' . $res->toInt() . ' Coalesce with constants');
        return $res->toInt();
    }

    private function processTernary(): int {
        display('propagating Constant value in Ternary');
        $this->atomIs('Ternary')
             ->hasNo('propagated')
             // Split CONDITION, THEN and ELSE to ensure order
             ->filter(
                 $this->side()
                      ->outIs('CONDITION')
                      ->has('intval')
                      ->savePropertyAs(savePropertyAs::ATOM, 'condition')
             )
             ->filter(
                 $this->side()
                      ->outIs('THEN')
                      ->has('intval')
                      ->savePropertyAs(savePropertyAs::ATOM, '_then')
             )
             ->filter(
                 $this->side()
                      ->outIs('ELSE')
                      ->has('intval')
                      ->savePropertyAs(savePropertyAs::ATOM, '_else')
             )
             ->raw(<<<'GREMLIN'
sideEffect{ 
    if (condition.value("intval") == 0) {
      if (_then.label() == 'Void') {
          i = condition.value("intval");
      } else {
          i = _then.value("intval");
      }
    } else {
      i = _else.value("intval");
    }

    it.get().property("boolean", i != 0);
    it.get().property("noDelimiter", i.toString()); 
    it.get().property("intval", i); 
    it.get().property("propagated", true); 

    i = null;
}
GREMLIN
             )
           ->count();
        $res = $this->rawQuery();

        display('propagating ' . $res->toInt() . ' Ternary with constants');
        return $res->toInt();
    }

    private function processBitshift(): int {
        display('propagating Constant value in Bitshift');
        $this->atomIs('Bitshift')
             ->hasNo('propagated')
             // Split LEFT and RIGHT to ensure left is in 0
             ->filter(
                 $this->side()
                      ->outIs('LEFT')
                      ->has('intval')
                      ->savePropertyAs(savePropertyAs::ATOM, 'left')
             )
             ->filter(
                 $this->side()
                      ->outIs('RIGHT')
                      ->has('intval')
                      ->savePropertyAs(savePropertyAs::ATOM, 'right')
             )
             ->raw(<<<'GREMLIN'
sideEffect{ 
    if (it.get().value("token") == 'T_SL') {
      i = left.value("intval") << right.value("intval");
    } else if (it.get().value("token") == 'T_SR') {
      i = left.value("intval") >> right.value("intval");
    }
    
    it.get().property("intval", i); 
    it.get().property("boolean", i != 0);
    it.get().property("noDelimiter", i.toString()); 
    it.get().property("propagated", true); 

    i = null;
}
GREMLIN
             )
           ->count();
        $res = $this->rawQuery();

        display('propagating ' . $res->toInt() . ' Bitshift with constants');
        return $res->toInt();
    }

    private function processMultiplication(): int {
        display('propagating Constant value in Multiplication');
        $this->atomIs('Multiplication')
             ->hasNo('propagated')
             // Split LEFT and RIGHT to ensure left is in 0
             ->filter(
                 $this->side()
                      ->outIs('LEFT')
                      ->has('intval')
                      ->savePropertyAs(savePropertyAs::ATOM, 'left')
             )
             ->filter(
                 $this->side()
                      ->outIs('RIGHT')
                      ->has('intval')
                      ->savePropertyAs(savePropertyAs::ATOM, 'right')
             )
             ->raw(<<<'GREMLIN'
sideEffect{ 
    if (it.get().value("token") == 'T_STAR') {
       i = left.value("intval") * right.value("intval");
    } else if (it.get().value("token") == 'T_SLASH') {
      if (right.value("intval") != 0) {
          i = left.value("intval") / right.value("intval");
          i = i.setScale(0, BigDecimal.ROUND_HALF_DOWN).toInteger();
      } else {
          i = 0;
      }
    } else if (it.get().value("token") == 'T_PERCENTAGE') {
      if (right.value("intval") != 0) {
          i = left.value("intval") % right.value("intval");
      } else {
          i = 0;
      }
    } 
    
    it.get().property("intval", i); 
    it.get().property("boolean", i != 0);
    it.get().property("noDelimiter", i.toString()); 
    it.get().property("propagated", true); 

    i = null;
}
GREMLIN
             )
           ->count();
        $res = $this->rawQuery();

        display('propagating ' . $res->toInt() . ' Multiplication with constants');
        return $res->toInt();
    }
}

?>
