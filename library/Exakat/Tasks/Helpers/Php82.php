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


class Php82 extends Php {
    // PHP tokens
    public const T_LNUMBER                                          = 260;
    public const T_DNUMBER                                          = 261;
    public const T_STRING                                           = 262;
    public const T_NAME_FULLY_QUALIFIED                             = 263;
    public const T_NAME_RELATIVE                                    = 264;
    public const T_NAME_QUALIFIED                                   = 265;
    public const T_VARIABLE                                         = 266;
    public const T_INLINE_HTML                                      = 267;
    public const T_ENCAPSED_AND_WHITESPACE                          = 268;
    public const T_CONSTANT_ENCAPSED_STRING                         = 269;
    public const T_STRING_VARNAME                                   = 270;
    public const T_NUM_STRING                                       = 271;
    public const T_INCLUDE                                          = 272;
    public const T_INCLUDE_ONCE                                     = 273;
    public const T_EVAL                                             = 274;
    public const T_REQUIRE                                          = 275;
    public const T_REQUIRE_ONCE                                     = 276;
    public const T_LOGICAL_OR                                       = 277;
    public const T_LOGICAL_XOR                                      = 278;
    public const T_LOGICAL_AND                                      = 279;
    public const T_PRINT                                            = 280;
    public const T_YIELD                                            = 281;
    public const T_YIELD_FROM                                       = 282;
    public const T_INSTANCEOF                                       = 283;
    public const T_NEW                                              = 284;
    public const T_CLONE                                            = 285;
    public const T_EXIT                                             = 286;
    public const T_IF                                               = 287;
    public const T_ELSEIF                                           = 288;
    public const T_ELSE                                             = 289;
    public const T_ENDIF                                            = 290;
    public const T_ECHO                                             = 291;
    public const T_DO                                               = 292;
    public const T_WHILE                                            = 293;
    public const T_ENDWHILE                                         = 294;
    public const T_FOR                                              = 295;
    public const T_ENDFOR                                           = 296;
    public const T_FOREACH                                          = 297;
    public const T_ENDFOREACH                                       = 298;
    public const T_DECLARE                                          = 299;
    public const T_ENDDECLARE                                       = 300;
    public const T_AS                                               = 301;
    public const T_SWITCH                                           = 302;
    public const T_ENDSWITCH                                        = 303;
    public const T_CASE                                             = 304;
    public const T_DEFAULT                                          = 305;
    public const T_MATCH                                            = 306;
    public const T_BREAK                                            = 307;
    public const T_CONTINUE                                         = 308;
    public const T_GOTO                                             = 309;
    public const T_FUNCTION                                         = 310;
    public const T_FN                                               = 311;
    public const T_CONST                                            = 312;
    public const T_RETURN                                           = 313;
    public const T_TRY                                              = 314;
    public const T_CATCH                                            = 315;
    public const T_FINALLY                                          = 316;
    public const T_THROW                                            = 317;
    public const T_USE                                              = 318;
    public const T_INSTEADOF                                        = 319;
    public const T_GLOBAL                                           = 320;
    public const T_STATIC                                           = 321;
    public const T_ABSTRACT                                         = 322;
    public const T_FINAL                                            = 323;
    public const T_PRIVATE                                          = 324;
    public const T_PROTECTED                                        = 325;
    public const T_PUBLIC                                           = 326;
    public const T_READONLY                                         = 327;
    public const T_VAR                                              = 328;
    public const T_UNSET                                            = 329;
    public const T_ISSET                                            = 330;
    public const T_EMPTY                                            = 331;
    public const T_HALT_COMPILER                                    = 332;
    public const T_CLASS                                            = 333;
    public const T_TRAIT                                            = 334;
    public const T_INTERFACE                                        = 335;
    public const T_ENUM                                             = 336;
    public const T_EXTENDS                                          = 337;
    public const T_IMPLEMENTS                                       = 338;
    public const T_NAMESPACE                                        = 339;
    public const T_LIST                                             = 340;
    public const T_ARRAY                                            = 341;
    public const T_CALLABLE                                         = 342;
    public const T_LINE                                             = 343;
    public const T_FILE                                             = 344;
    public const T_DIR                                              = 345;
    public const T_CLASS_C                                          = 346;
    public const T_TRAIT_C                                          = 347;
    public const T_METHOD_C                                         = 348;
    public const T_FUNC_C                                           = 349;
    public const T_NS_C                                             = 350;
    public const T_ATTRIBUTE                                        = 351;
    public const T_PLUS_EQUAL                                       = 352;
    public const T_MINUS_EQUAL                                      = 353;
    public const T_MUL_EQUAL                                        = 354;
    public const T_DIV_EQUAL                                        = 355;
    public const T_CONCAT_EQUAL                                     = 356;
    public const T_MOD_EQUAL                                        = 357;
    public const T_AND_EQUAL                                        = 358;
    public const T_OR_EQUAL                                         = 359;
    public const T_XOR_EQUAL                                        = 360;
    public const T_SL_EQUAL                                         = 361;
    public const T_SR_EQUAL                                         = 362;
    public const T_COALESCE_EQUAL                                   = 363;
    public const T_BOOLEAN_OR                                       = 364;
    public const T_BOOLEAN_AND                                      = 365;
    public const T_IS_EQUAL                                         = 366;
    public const T_IS_NOT_EQUAL                                     = 367;
    public const T_IS_IDENTICAL                                     = 368;
    public const T_IS_NOT_IDENTICAL                                 = 369;
    public const T_IS_SMALLER_OR_EQUAL                              = 370;
    public const T_IS_GREATER_OR_EQUAL                              = 371;
    public const T_SPACESHIP                                        = 372;
    public const T_SL                                               = 373;
    public const T_SR                                               = 374;
    public const T_INC                                              = 375;
    public const T_DEC                                              = 376;
    public const T_INT_CAST                                         = 377;
    public const T_DOUBLE_CAST                                      = 378;
    public const T_STRING_CAST                                      = 379;
    public const T_ARRAY_CAST                                       = 380;
    public const T_OBJECT_CAST                                      = 381;
    public const T_BOOL_CAST                                        = 382;
    public const T_UNSET_CAST                                       = 383;
    public const T_OBJECT_OPERATOR                                  = 384;
    public const T_NULLSAFE_OBJECT_OPERATOR                         = 385;
    public const T_DOUBLE_ARROW                                     = 386;
    public const T_COMMENT                                          = 387;
    public const T_DOC_COMMENT                                      = 388;
    public const T_OPEN_TAG                                         = 389;
    public const T_OPEN_TAG_WITH_ECHO                               = 390;
    public const T_CLOSE_TAG                                        = 391;
    public const T_WHITESPACE                                       = 392;
    public const T_START_HEREDOC                                    = 393;
    public const T_END_HEREDOC                                      = 394;
    public const T_DOLLAR_OPEN_CURLY_BRACES                         = 395;
    public const T_CURLY_OPEN                                       = 396;
    public const T_PAAMAYIM_NEKUDOTAYIM                             = 397;
    public const T_NS_SEPARATOR                                     = 398;
    public const T_ELLIPSIS                                         = 399;
    public const T_COALESCE                                         = 400;
    public const T_POW                                              = 401;
    public const T_POW_EQUAL                                        = 402;
    public const T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG              = 403;
    public const T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG          = 404;
    public const T_BAD_CHARACTER                                    = 405;
    public const T_DOUBLE_COLON                                     = 397;
}
?>
