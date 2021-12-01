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

class Php72 extends Php {

    // PHP tokens
    public const T_REQUIRE_ONCE                  = 262;
    public const T_REQUIRE                       = 261;
    public const T_EVAL                          = 260;
    public const T_INCLUDE_ONCE                  = 259;
    public const T_INCLUDE                       = 258;
    public const T_LOGICAL_OR                    = 263;
    public const T_LOGICAL_XOR                   = 264;
    public const T_LOGICAL_AND                   = 265;
    public const T_PRINT                         = 266;
    public const T_YIELD                         = 267;
    public const T_DOUBLE_ARROW                  = 268;
    public const T_YIELD_FROM                    = 269;
    public const T_POW_EQUAL                     = 281;
    public const T_SR_EQUAL                      = 280;
    public const T_SL_EQUAL                      = 279;
    public const T_XOR_EQUAL                     = 278;
    public const T_OR_EQUAL                      = 277;
    public const T_AND_EQUAL                     = 276;
    public const T_MOD_EQUAL                     = 275;
    public const T_CONCAT_EQUAL                  = 274;
    public const T_DIV_EQUAL                     = 273;
    public const T_MUL_EQUAL                     = 272;
    public const T_MINUS_EQUAL                   = 271;
    public const T_PLUS_EQUAL                    = 270;
    public const T_COALESCE                      = 282;
    public const T_BOOLEAN_OR                    = 283;
    public const T_BOOLEAN_AND                   = 284;
    public const T_SPACESHIP                     = 289;
    public const T_IS_NOT_IDENTICAL              = 288;
    public const T_IS_IDENTICAL                  = 287;
    public const T_IS_NOT_EQUAL                  = 286;
    public const T_IS_EQUAL                      = 285;
    public const T_IS_GREATER_OR_EQUAL           = 291;
    public const T_IS_SMALLER_OR_EQUAL           = 290;
    public const T_SR                            = 293;
    public const T_SL                            = 292;
    public const T_INSTANCEOF                    = 294;
    public const T_UNSET_CAST                    = 303;
    public const T_BOOL_CAST                     = 302;
    public const T_OBJECT_CAST                   = 301;
    public const T_ARRAY_CAST                    = 300;
    public const T_STRING_CAST                   = 299;
    public const T_DOUBLE_CAST                   = 298;
    public const T_INT_CAST                      = 297;
    public const T_DEC                           = 296;
    public const T_INC                           = 295;
    public const T_POW                           = 304;
    public const T_CLONE                         = 306;
    public const T_NEW                           = 305;
    public const T_ELSEIF                        = 308;
    public const T_ELSE                          = 309;
    public const T_ENDIF                         = 310;
    public const T_PUBLIC                        = 316;
    public const T_PROTECTED                     = 315;
    public const T_PRIVATE                       = 314;
    public const T_FINAL                         = 313;
    public const T_ABSTRACT                      = 312;
    public const T_STATIC                        = 311;
    public const T_LNUMBER                       = 317;
    public const T_DNUMBER                       = 318;
    public const T_STRING                        = 319;
    public const T_VARIABLE                      = 320;
    public const T_INLINE_HTML                   = 321;
    public const T_ENCAPSED_AND_WHITESPACE       = 322;
    public const T_CONSTANT_ENCAPSED_STRING      = 323;
    public const T_STRING_VARNAME                = 324;
    public const T_NUM_STRING                    = 325;
    public const T_EXIT                          = 326;
    public const T_IF                            = 327;
    public const T_ECHO                          = 328;
    public const T_DO                            = 329;
    public const T_WHILE                         = 330;
    public const T_ENDWHILE                      = 331;
    public const T_FOR                           = 332;
    public const T_ENDFOR                        = 333;
    public const T_FOREACH                       = 334;
    public const T_ENDFOREACH                    = 335;
    public const T_DECLARE                       = 336;
    public const T_ENDDECLARE                    = 337;
    public const T_AS                            = 338;
    public const T_SWITCH                        = 339;
    public const T_ENDSWITCH                     = 340;
    public const T_CASE                          = 341;
    public const T_DEFAULT                       = 342;
    public const T_BREAK                         = 343;
    public const T_CONTINUE                      = 344;
    public const T_GOTO                          = 345;
    public const T_FUNCTION                      = 346;
    public const T_CONST                         = 347;
    public const T_RETURN                        = 348;
    public const T_TRY                           = 349;
    public const T_CATCH                         = 350;
    public const T_FINALLY                       = 351;
    public const T_THROW                         = 352;
    public const T_USE                           = 353;
    public const T_INSTEADOF                     = 354;
    public const T_GLOBAL                        = 355;
    public const T_VAR                           = 356;
    public const T_UNSET                         = 357;
    public const T_ISSET                         = 358;
    public const T_EMPTY                         = 359;
    public const T_HALT_COMPILER                 = 360;
    public const T_CLASS                         = 361;
    public const T_TRAIT                         = 362;
    public const T_INTERFACE                     = 363;
    public const T_EXTENDS                       = 364;
    public const T_IMPLEMENTS                    = 365;
    public const T_OBJECT_OPERATOR               = 366;
    public const T_LIST                          = 367;
    public const T_ARRAY                         = 368;
    public const T_CALLABLE                      = 369;
    public const T_LINE                          = 370;
    public const T_FILE                          = 371;
    public const T_DIR                           = 372;
    public const T_CLASS_C                       = 373;
    public const T_TRAIT_C                       = 374;
    public const T_METHOD_C                      = 375;
    public const T_FUNC_C                        = 376;
    public const T_COMMENT                       = 377;
    public const T_DOC_COMMENT                   = 378;
    public const T_OPEN_TAG                      = 379;
    public const T_OPEN_TAG_WITH_ECHO            = 380;
    public const T_CLOSE_TAG                     = 381;
    public const T_WHITESPACE                    = 382;
    public const T_START_HEREDOC                 = 383;
    public const T_END_HEREDOC                   = 384;
    public const T_DOLLAR_OPEN_CURLY_BRACES      = 385;
    public const T_CURLY_OPEN                    = 386;
    public const T_PAAMAYIM_NEKUDOTAYIM          = 387;
    public const T_NAMESPACE                     = 388;
    public const T_NS_C                          = 389;
    public const T_NS_SEPARATOR                  = 390;
    public const T_ELLIPSIS                      = 391;
    public const T_DOUBLE_COLON                  = 387;
    public const T_COALESCE_EQUAL                = 1000;
    public const T_FN                            = 1000;
    public const T_BAD_CHARACTER                 = 1000;
    public const T_CHARACTER                     = 1000;
    public const T_NAME_FULLY_QUALIFIED          = 1000;
    public const T_NAME_RELATIVE                 = 1000;
    public const T_NAME_QUALIFIED                = 1000;
    public const T_NULLSAFE_OBJECT_OPERATOR      = 1000;
    public const T_MATCH                         = 1000;
    public const T_ATTRIBUTE                     = 1000;
    public const T_ENUM                                             = 1000;
    public const T_READONLY                                         = 1000;
    public const T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG              = 1000;
    public const T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG          = 1000;
}
?>
