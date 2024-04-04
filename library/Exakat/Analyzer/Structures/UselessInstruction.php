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


namespace Exakat\Analyzer\Structures;

use Exakat\Analyzer\Analyzer;
use Exakat\Query\DSL\FollowParAs;

class UselessInstruction extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/SetClassMethodRemoteDefinition',
                     'Classes/IsaMagicProperty',
                    );
    }

    public function analyze(): void {
        // Structures that should be put somewhere, and never left alone
        $this->atomIs('Sequence')
             ->hasNoIn('FINAL')
             ->hasNoParent('Match', array('CODE', 'EXPRESSION', 'CASES'))
             ->outIs('EXPRESSION')
             ->analyzerIsNot('Classes/IsaMagicProperty')
             ->atomIs(array('Array', 'Addition', 'Multiplication', 'Member', 'Staticproperty', 'Boolean',
                            'Magicconstant', 'Staticconstant', 'Integer', 'Float', 'Sign', 'Nsname',
                            'Identifier', 'String', 'Instanceof', 'Bitshift', 'Comparison', 'Null', 'Logical', 'Bitoperation',
                            'Heredoc', 'Power', 'Coalesce', 'Ternary', 'Variable', 'Arrayliteral', 'New', 'Match', 'Spaceship',
                            'Clone'))
             ->noAtomInside(array('Functioncall', 'Staticmethodcall', 'Methodcall', 'Assignation', 'Defineconstant'));
        $this->prepareQuery();

        // foreach($i = 0; $i < 10, $j < 20; $i++)
        $this->atomIs('For')
             ->outIs('FINAL')
             ->outWithoutLastRank()
             ->atomIs(array('Array', 'Addition', 'Multiplication', 'Member', 'Staticproperty', 'Boolean',
                            'Magicconstant', 'Staticconstant', 'Integer', 'Float', 'Sign', 'Nsname',
                            'Identifier', 'String', 'Instanceof', 'Bitshift', 'Comparison', 'Null', 'Logical',
                            'Heredoc', 'Power', 'Coalesce', 'New'))
             ->noAtomInside(array('Functioncall', 'Staticmethodcall', 'Methodcall', 'Assignation', ));
        $this->prepareQuery();

        // -$x = 3
        $this->atomIs('Assignation')
             ->outIs('LEFT')
             ->atomIs('Sign');
        $this->prepareQuery();

        // closures that are not assigned to something (argument or variable)
        $this->atomIs('Sequence')
             ->outIs('EXPRESSION')
             ->atomIs(array('Closure', 'Arrowfunction'));
        $this->prepareQuery();

        // return $a++; (unless it is an argument/use by reference)
        // May also check if it is static or global (those stays).
        $this->atomIs('Return')
             ->outIs('RETURN')
             ->atomIs('Postplusplus')
             ->outIs('POSTPLUSPLUS')
             ->atomIsNot(array('Member', 'Staticproperty'))
             ->not(
                 $this->side()
                      ->atomIs('Variable')
                      ->inIs('DEFINITION')
                      ->outIs('DEFINITION')
                      ->atomIs(array('Staticdefinition', 'Globaldefinition'))
             )
             ->not(
                 $this->side()
                      ->atomIs('Variable')
                      ->inIs('DEFINITION')
                      ->inIsIE('NAME')
                      ->is('reference', true)
             )
             ->back('first');
        $this->prepareQuery();

        // return an argument that is also a reference
        $this->atomIs('Return')
             ->analyzerIsNot('self')
             ->outIs('RETURN')
             ->atomIs('Postplusplus')
             ->outIs('POSTPLUSPLUS')
             ->atomIs('Variable')
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('DEFINITION')
                      ->atomIs(array('Staticdefinition', 'Globaldefinition'))
             )
             ->not(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->inIsIE('NAME')
                      ->is('reference', true)
             )
             ->back('first');
        $this->prepareQuery();

        // return an assigned variable
        // @todo : add support for static, referenc argument, global
        $this->atomIs('Return')
             ->analyzerIsNot('self')
             ->atomInsideNoDefinition('Assignation')
             ->outIs('LEFT')
             ->atomIsNot(array('Member', 'Staticproperty', 'Phpvariable'))
             ->hasNoChildren(array('Member', 'Staticproperty', 'Phpvariable'), array('VARIABLE'))
             ->hasNoChildren(array('Member', 'Staticproperty', 'Phpvariable'), array('VARIABLE', 'VARIABLE'))
             ->savePropertyAs('code', 'variable')
            // It is not an argument with reference
             ->isReferencedArgument('variable')
            // it is not a global nor a static
             ->not(
                 $this->side()
                      ->atomIs('Variable')
                      ->inIs('DEFINITION')
                      ->outIs('DEFINITION')
                      ->atomIs(array('Staticdefinition', 'Globaldefinition'))
             )
             ->back('first');
        $this->prepareQuery();

        // array_merge($a); one argument is useless.
        $this->atomFunctionIs('\\array_replace')
             ->isLess('count', 2)
             ->outWithRank('ARGUMENT',0)
             ->isNot('variadic', true)
             ->back('first');
        $this->prepareQuery();

        // foreach(@$a as $b);
        $this->atomIs('Foreach')
             ->outIs('SOURCE')
             ->is('noscream', true);
        $this->prepareQuery();

        // @$x = 3;
        $this->atomIs('Assignation')
             ->outIs('LEFT')
             ->is('noscream', true);
        $this->prepareQuery();

        // Closure with some operations
        $this->atomIs('Function')
             ->inIs('LEFT')
             ->atomIs(array('Addition', 'Multiplication', 'Power'))
             ->back('first');
        $this->prepareQuery();

        // $x = 'a' . function ($a) {} (Concatenating a closure...)
        $this->atomIs('Function')
             ->inIs('CONCAT')
             ->atomIs('Concatenation')
             ->back('first');
        $this->prepareQuery();

        // New in a instanceof (with/without parenthesis)
        $this->atomIs('New')
             ->inIsIE(array('CODE', 'RIGHT'))
             ->inIs('VARIABLE')
             ->atomIs('Instanceof')
             ->back('first');
        $this->prepareQuery();

        // New in a clone
        $this->atomIs('Clone')
             ->analyzerIsNot('self')
             ->outIs('CLONE')
             ->followParAs(FollowParAs::FOLLOW_PARAS_TERNARY)
             ->atomIs(array('New', 'Clone'))
             ->back('first');
        $this->prepareQuery();

        // Empty string in a concatenation
        $this->atomIs('Concatenation')
             ->outIs('CONCAT')
             ->outIsIE('CODE') // skip parenthesis
             ->codeIs(array("''", '""'))
             ->back('first');
        $this->prepareQuery();

        // array_unique(array_keys())
        $this->atomFunctionIs('\\array_unique')
             ->outWithRank('ARGUMENT', 0)
             ->functioncallIs('\\array_keys')
             ->back('first');
        $this->prepareQuery();

        $this->atomFunctionIs('\\count')
             ->outWithRank('ARGUMENT', 0)
             ->functioncallIs(array('\\array_keys',
                                    '\\array_values',
                                    '\\array_flip',
                                    '\\array_fill',
                                    '\\array_walk',
                                    '\\array_map',
                                    '\\array_change_key_case',
                                    '\\array_combine',
                                    '\\array_multisort',
                                    '\\array_replace',
                                    '\\array_reverse',
                                    ))
             ->back('first');
        $this->prepareQuery();

        // $a = $b ? 'c' : $a = 3;
        $this->atomIs('Assignation')
             ->outIs('LEFT')
             ->savePropertyAs('fullcode', 'var')
             ->back('first')

             ->outIs('RIGHT')
             ->atomIs(array('Ternary', 'Coalesce'))
             ->outIs(array('THEN', 'ELSE', 'RIGHT'))
             ->atomIs('Assignation')
             ->outIs('LEFT')
             ->samePropertyAs('fullcode', 'var')
             ->back('first');
        $this->prepareQuery();

        // $a ?? null
        $this->atomIs('Coalesce')
             ->analyzerIsNot('self')
             ->outIs('RIGHT')
             ->outIsIE('CODE')
             ->atomIsNot(array('Coalesce', 'Ternary')) // ternary should be check on both sides of the branch
             ->atomIs('Null', self::WITH_CONSTANTS)
             ->back('first');
        $this->prepareQuery();
    }
}

?>
