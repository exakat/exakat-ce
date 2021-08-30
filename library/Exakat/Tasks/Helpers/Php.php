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

namespace Exakat\Tasks\Helpers;

use Exakat\Exceptions\NoRecognizedTokens;

abstract class Php {

// Exakat home-made tokens
    public const T_BANG                         = '!';
    public const T_CLOSE_BRACKET                = ']';
    public const T_CLOSE_PARENTHESIS            = ')';
    public const T_CLOSE_CURLY                  = '}';
    public const T_COMMA                        = ',';
    public const T_DOT                          = '.';
    public const T_EQUAL                        = '=';
    public const T_MINUS                        = '-';
    public const T_AT                           = '@';
    public const T_OPEN_BRACKET                 = '[';
    public const T_OPEN_CURLY                   = '{';
    public const T_OPEN_PARENTHESIS             = '(';
    public const T_PERCENTAGE                   = '%';
    public const T_PLUS                         = '+';
    public const T_QUESTION                     = '?';
    public const T_COLON                        = ':';
    public const T_SEMICOLON                    = ';';
    public const T_SLASH                        = '/';
    public const T_STAR                         = '*';
    public const T_SMALLER                      = '<';
    public const T_GREATER                      = '>';
    public const T_TILDE                        = '~';
    public const T_QUOTE                        = '"';
    public const T_DOLLAR                       = '$';
    public const T_AND                          = '&';
    public const T_BACKTICK                     = '`';
    public const T_OR                           = '|';
    public const T_XOR                          = '^';
    public const T_ANDAND                       = '&&';
    public const T_OROR                         = '||';
    public const T_QUOTE_CLOSE                  = '"_CLOSE';
    public const T_SHELL_QUOTE                  = '`';
    public const T_SHELL_QUOTE_CLOSE            = '`_CLOSE';

    public const T_END                          = 'The End';
    public const T_REFERENCE                    = 'r';
    public const T_VOID                         = 'v';

    public const TOKENS = array(
                     ';'  => self::T_SEMICOLON,
                     '+'  => self::T_PLUS,
                     '-'  => self::T_MINUS,
                     '/'  => self::T_SLASH,
                     '*'  => self::T_STAR,
                     '.'  => self::T_DOT,
                     '['  => self::T_OPEN_BRACKET,
                     ']'  => self::T_CLOSE_BRACKET,
                     '('  => self::T_OPEN_PARENTHESIS,
                     ')'  => self::T_CLOSE_PARENTHESIS,
                     '{'  => self::T_OPEN_CURLY,
                     '}'  => self::T_CLOSE_CURLY,
                     '='  => self::T_EQUAL,
                     ','  => self::T_COMMA,
                     '!'  => self::T_BANG,
                     '~'  => self::T_TILDE,
                     '@'  => self::T_AT,
                     '?'  => self::T_QUESTION,
                     ':'  => self::T_COLON,
                     '<'  => self::T_SMALLER,
                     '>'  => self::T_GREATER,
                     '%'  => self::T_PERCENTAGE,
                     '"'  => self::T_QUOTE,
                     'b"' => self::T_QUOTE,
                     '$'  => self::T_DOLLAR,
                     '&'  => self::T_AND,
                     '|'  => self::T_OR,
                     '^'  => self::T_XOR,
                     '`'  => self::T_BACKTICK,
                   );

    public static function getInstance($tokens): self {
        $errors = array();

        if (empty($tokens)) {
            throw new NoRecognizedTokens($tokens);
        }

        $versions = array('Php81', 'Php80', 'Php74', 'Php73', 'Php72', 'Php71', 'Php70', 'Php56', 'Php55', );

        foreach($versions as $version) {
            $errors = array();
            foreach($tokens as $k => $v) {
                if (constant(__NAMESPACE__ . "\\$version::$v") !== $k) {
                    $errors[$k] = $v;
                }
            }

            if (empty($errors)) {
                $className = __NAMESPACE__ . "\\$version";
                return new $className();
            }
        }

        throw new NoRecognizedTokens();
    }
}
?>
