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

    // PHP tokens : Here for overloading in by PHP** classes.
    public const T_LNUMBER                                          = -1;
    public const T_DNUMBER                                          = -1;
    public const T_STRING                                           = -1;
    public const T_NAME_FULLY_QUALIFIED                             = -1;
    public const T_NAME_RELATIVE                                    = -1;
    public const T_NAME_QUALIFIED                                   = -1;
    public const T_VARIABLE                                         = -1;
    public const T_INLINE_HTML                                      = -1;
    public const T_ENCAPSED_AND_WHITESPACE                          = -1;
    public const T_CONSTANT_ENCAPSED_STRING                         = -1;
    public const T_STRING_VARNAME                                   = -1;
    public const T_NUM_STRING                                       = -1;
    public const T_INCLUDE                                          = -1;
    public const T_INCLUDE_ONCE                                     = -1;
    public const T_EVAL                                             = -1;
    public const T_REQUIRE                                          = -1;
    public const T_REQUIRE_ONCE                                     = -1;
    public const T_LOGICAL_OR                                       = -1;
    public const T_LOGICAL_XOR                                      = -1;
    public const T_LOGICAL_AND                                      = -1;
    public const T_PRINT                                            = -1;
    public const T_YIELD                                            = -1;
    public const T_YIELD_FROM                                       = -1;
    public const T_INSTANCEOF                                       = -1;
    public const T_NEW                                              = -1;
    public const T_CLONE                                            = -1;
    public const T_EXIT                                             = -1;
    public const T_IF                                               = -1;
    public const T_ELSEIF                                           = -1;
    public const T_ELSE                                             = -1;
    public const T_ENDIF                                            = -1;
    public const T_ECHO                                             = -1;
    public const T_DO                                               = -1;
    public const T_WHILE                                            = -1;
    public const T_ENDWHILE                                         = -1;
    public const T_FOR                                              = -1;
    public const T_ENDFOR                                           = -1;
    public const T_FOREACH                                          = -1;
    public const T_ENDFOREACH                                       = -1;
    public const T_DECLARE                                          = -1;
    public const T_ENDDECLARE                                       = -1;
    public const T_AS                                               = -1;
    public const T_SWITCH                                           = -1;
    public const T_ENDSWITCH                                        = -1;
    public const T_CASE                                             = -1;
    public const T_DEFAULT                                          = -1;
    public const T_MATCH                                            = -1;
    public const T_BREAK                                            = -1;
    public const T_CONTINUE                                         = -1;
    public const T_GOTO                                             = -1;
    public const T_FUNCTION                                         = -1;
    public const T_FN                                               = -1;
    public const T_CONST                                            = -1;
    public const T_RETURN                                           = -1;
    public const T_TRY                                              = -1;
    public const T_CATCH                                            = -1;
    public const T_FINALLY                                          = -1;
    public const T_THROW                                            = -1;
    public const T_USE                                              = -1;
    public const T_INSTEADOF                                        = -1;
    public const T_GLOBAL                                           = -1;
    public const T_STATIC                                           = -1;
    public const T_ABSTRACT                                         = -1;
    public const T_FINAL                                            = -1;
    public const T_PRIVATE                                          = -1;
    public const T_PROTECTED                                        = -1;
    public const T_PUBLIC                                           = -1;
    public const T_READONLY                                         = -1;
    public const T_VAR                                              = -1;
    public const T_UNSET                                            = -1;
    public const T_ISSET                                            = -1;
    public const T_EMPTY                                            = -1;
    public const T_HALT_COMPILER                                    = -1;
    public const T_CLASS                                            = -1;
    public const T_TRAIT                                            = -1;
    public const T_INTERFACE                                        = -1;
    public const T_ENUM                                             = -1;
    public const T_EXTENDS                                          = -1;
    public const T_IMPLEMENTS                                       = -1;
    public const T_NAMESPACE                                        = -1;
    public const T_LIST                                             = -1;
    public const T_ARRAY                                            = -1;
    public const T_CALLABLE                                         = -1;
    public const T_LINE                                             = -1;
    public const T_FILE                                             = -1;
    public const T_DIR                                              = -1;
    public const T_CLASS_C                                          = -1;
    public const T_TRAIT_C                                          = -1;
    public const T_METHOD_C                                         = -1;
    public const T_FUNC_C                                           = -1;
    public const T_NS_C                                             = -1;
    public const T_ATTRIBUTE                                        = -1;
    public const T_PLUS_EQUAL                                       = -1;
    public const T_MINUS_EQUAL                                      = -1;
    public const T_MUL_EQUAL                                        = -1;
    public const T_DIV_EQUAL                                        = -1;
    public const T_CONCAT_EQUAL                                     = -1;
    public const T_MOD_EQUAL                                        = -1;
    public const T_AND_EQUAL                                        = -1;
    public const T_OR_EQUAL                                         = -1;
    public const T_XOR_EQUAL                                        = -1;
    public const T_SL_EQUAL                                         = -1;
    public const T_SR_EQUAL                                         = -1;
    public const T_COALESCE_EQUAL                                   = -1;
    public const T_BOOLEAN_OR                                       = -1;
    public const T_BOOLEAN_AND                                      = -1;
    public const T_IS_EQUAL                                         = -1;
    public const T_IS_NOT_EQUAL                                     = -1;
    public const T_IS_IDENTICAL                                     = -1;
    public const T_IS_NOT_IDENTICAL                                 = -1;
    public const T_IS_SMALLER_OR_EQUAL                              = -1;
    public const T_IS_GREATER_OR_EQUAL                              = -1;
    public const T_SPACESHIP                                        = -1;
    public const T_SL                                               = -1;
    public const T_SR                                               = -1;
    public const T_INC                                              = -1;
    public const T_DEC                                              = -1;
    public const T_INT_CAST                                         = -1;
    public const T_DOUBLE_CAST                                      = -1;
    public const T_STRING_CAST                                      = -1;
    public const T_ARRAY_CAST                                       = -1;
    public const T_OBJECT_CAST                                      = -1;
    public const T_BOOL_CAST                                        = -1;
    public const T_UNSET_CAST                                       = -1;
    public const T_OBJECT_OPERATOR                                  = -1;
    public const T_NULLSAFE_OBJECT_OPERATOR                         = -1;
    public const T_DOUBLE_ARROW                                     = -1;
    public const T_COMMENT                                          = -1;
    public const T_DOC_COMMENT                                      = -1;
    public const T_OPEN_TAG                                         = -1;
    public const T_OPEN_TAG_WITH_ECHO                               = -1;
    public const T_CLOSE_TAG                                        = -1;
    public const T_WHITESPACE                                       = -1;
    public const T_START_HEREDOC                                    = -1;
    public const T_END_HEREDOC                                      = -1;
    public const T_DOLLAR_OPEN_CURLY_BRACES                         = -1;
    public const T_CURLY_OPEN                                       = -1;
    public const T_PAAMAYIM_NEKUDOTAYIM                             = -1;
    public const T_NS_SEPARATOR                                     = -1;
    public const T_ELLIPSIS                                         = -1;
    public const T_COALESCE                                         = -1;
    public const T_POW                                              = -1;
    public const T_POW_EQUAL                                        = -1;
    public const T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG              = -1;
    public const T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG          = -1;
    public const T_BAD_CHARACTER                                    = -1;
    public const T_DOUBLE_COLON                                     = -1;
    public const T_CHARACTER                                        = -1;

    public static function getInstance(array $tokens): self {
        $errors = array();

        if (empty($tokens)) {
            throw new NoRecognizedTokens('<No token provided>');
        }

        $versions = array('Php83', 'Php82', 'Php81', 'Php80', 'Php74', 'Php73', 'Php72', 'Php71', 'Php70', 'Php56', 'Php55', );

        foreach ($versions as $version) {
            $errors = array_filter($tokens,
                function (string $v, string $k) use ($version): bool {
                    return (string) constant(__NAMESPACE__ . '\\' . $version . '::' . $v) !== $k;
                },
                ARRAY_FILTER_USE_BOTH);

            if (empty($errors)) {
                $className = __NAMESPACE__ . "\\$version";
                return new $className();
            }
        }

        throw new NoRecognizedTokens(implode(', ', $errors));
    }
}
?>
