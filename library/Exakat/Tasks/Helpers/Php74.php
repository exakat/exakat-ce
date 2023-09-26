<?php declare(strict_types = 1);
/*
 * Copyright 2012-2021 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


class Php74 extends Php {
    // PHP tokens
    public const T_INCLUDE                                          = 269;
    public const T_INCLUDE_ONCE                                     = 270;
    public const T_REQUIRE                                          = 272;
    public const T_REQUIRE_ONCE                                     = 273;
    public const T_LOGICAL_OR                                       = 274;
    public const T_LOGICAL_XOR                                      = 275;
    public const T_LOGICAL_AND                                      = 276;
    public const T_PRINT                                            = 277;
    public const T_YIELD                                            = 278;
    public const T_DOUBLE_ARROW                                     = 366;
    public const T_YIELD_FROM                                       = 279;
    public const T_PLUS_EQUAL                                       = 280;
    public const T_MINUS_EQUAL                                      = 281;
    public const T_MUL_EQUAL                                        = 282;
    public const T_DIV_EQUAL                                        = 283;
    public const T_CONCAT_EQUAL                                     = 284;
    public const T_MOD_EQUAL                                        = 285;
    public const T_AND_EQUAL                                        = 286;
    public const T_OR_EQUAL                                         = 287;
    public const T_XOR_EQUAL                                        = 288;
    public const T_SL_EQUAL                                         = 289;
    public const T_SR_EQUAL                                         = 290;
    public const T_POW_EQUAL                                        = 394;
    public const T_COALESCE_EQUAL                                   = 291;
    public const T_COALESCE                                         = 392;
    public const T_BOOLEAN_OR                                       = 292;
    public const T_BOOLEAN_AND                                      = 293;
    public const T_IS_EQUAL                                         = 294;
    public const T_IS_NOT_EQUAL                                     = 295;
    public const T_IS_IDENTICAL                                     = 296;
    public const T_IS_NOT_IDENTICAL                                 = 297;
    public const T_SPACESHIP                                        = 300;
    public const T_IS_SMALLER_OR_EQUAL                              = 298;
    public const T_IS_GREATER_OR_EQUAL                              = 299;
    public const T_SL                                               = 301;
    public const T_SR                                               = 302;
    public const T_INSTANCEOF                                       = 303;
    public const T_INT_CAST                                         = 306;
    public const T_DOUBLE_CAST                                      = 307;
    public const T_STRING_CAST                                      = 308;
    public const T_ARRAY_CAST                                       = 309;
    public const T_OBJECT_CAST                                      = 310;
    public const T_BOOL_CAST                                        = 311;
    public const T_UNSET_CAST                                       = 312;
    public const T_POW                                              = 393;
    public const T_NEW                                              = 313;
    public const T_CLONE                                            = 314;
    public const T_ELSEIF                                           = 317;
    public const T_ELSE                                             = 318;
    public const T_LNUMBER                                          = 260;
    public const T_DNUMBER                                          = 261;
    public const T_STRING                                           = 262;
    public const T_VARIABLE                                         = 263;
    public const T_INLINE_HTML                                      = 264;
    public const T_ENCAPSED_AND_WHITESPACE                          = 265;
    public const T_CONSTANT_ENCAPSED_STRING                         = 266;
    public const T_STRING_VARNAME                                   = 267;
    public const T_NUM_STRING                                       = 268;
    public const T_EVAL                                             = 271;
    public const T_INC                                              = 304;
    public const T_DEC                                              = 305;
    public const T_EXIT                                             = 315;
    public const T_IF                                               = 316;
    public const T_ENDIF                                            = 319;
    public const T_ECHO                                             = 320;
    public const T_DO                                               = 321;
    public const T_WHILE                                            = 322;
    public const T_ENDWHILE                                         = 323;
    public const T_FOR                                              = 324;
    public const T_ENDFOR                                           = 325;
    public const T_FOREACH                                          = 326;
    public const T_ENDFOREACH                                       = 327;
    public const T_DECLARE                                          = 328;
    public const T_ENDDECLARE                                       = 329;
    public const T_AS                                               = 330;
    public const T_SWITCH                                           = 331;
    public const T_ENDSWITCH                                        = 332;
    public const T_CASE                                             = 333;
    public const T_DEFAULT                                          = 334;
    public const T_BREAK                                            = 335;
    public const T_CONTINUE                                         = 336;
    public const T_GOTO                                             = 337;
    public const T_FUNCTION                                         = 338;
    public const T_FN                                               = 339;
    public const T_CONST                                            = 340;
    public const T_RETURN                                           = 341;
    public const T_TRY                                              = 342;
    public const T_CATCH                                            = 343;
    public const T_FINALLY                                          = 344;
    public const T_THROW                                            = 345;
    public const T_USE                                              = 346;
    public const T_INSTEADOF                                        = 347;
    public const T_GLOBAL                                           = 348;
    public const T_STATIC                                           = 349;
    public const T_ABSTRACT                                         = 350;
    public const T_FINAL                                            = 351;
    public const T_PRIVATE                                          = 352;
    public const T_PROTECTED                                        = 353;
    public const T_PUBLIC                                           = 354;
    public const T_VAR                                              = 355;
    public const T_UNSET                                            = 356;
    public const T_ISSET                                            = 357;
    public const T_EMPTY                                            = 358;
    public const T_HALT_COMPILER                                    = 359;
    public const T_CLASS                                            = 360;
    public const T_TRAIT                                            = 361;
    public const T_INTERFACE                                        = 362;
    public const T_EXTENDS                                          = 363;
    public const T_IMPLEMENTS                                       = 364;
    public const T_OBJECT_OPERATOR                                  = 365;
    public const T_LIST                                             = 367;
    public const T_ARRAY                                            = 368;
    public const T_CALLABLE                                         = 369;
    public const T_LINE                                             = 370;
    public const T_FILE                                             = 371;
    public const T_DIR                                              = 372;
    public const T_CLASS_C                                          = 373;
    public const T_TRAIT_C                                          = 374;
    public const T_METHOD_C                                         = 375;
    public const T_FUNC_C                                           = 376;
    public const T_COMMENT                                          = 377;
    public const T_DOC_COMMENT                                      = 378;
    public const T_OPEN_TAG                                         = 379;
    public const T_OPEN_TAG_WITH_ECHO                               = 380;
    public const T_CLOSE_TAG                                        = 381;
    public const T_WHITESPACE                                       = 382;
    public const T_START_HEREDOC                                    = 383;
    public const T_END_HEREDOC                                      = 384;
    public const T_DOLLAR_OPEN_CURLY_BRACES                         = 385;
    public const T_CURLY_OPEN                                       = 386;
    public const T_PAAMAYIM_NEKUDOTAYIM                             = 387;
    public const T_NAMESPACE                                        = 388;
    public const T_NS_C                                             = 389;
    public const T_NS_SEPARATOR                                     = 390;
    public const T_ELLIPSIS                                         = 391;
    public const T_BAD_CHARACTER                                    = 395;
    public const T_DOUBLE_COLON                                     = 387;
}
?>
