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

use stdClass;
use Exakat\Tasks\Load;

abstract class AtomInterface {
    public $id           = 0;
    public $atom         = 'No Atom Set';
    public $code         = '';
    public $lccode       = '';
    public $fullcode     = '';
    public $line         = Load::NO_LINE;
    public $token        = '';
    public $rank         = ''; // Not 0
    public $rankName     = '';
    public $alternative  = Load::NOT_ALTERNATIVE;
    public $reference    = Load::NOT_REFERENCE;
    public $heredoc      = false;
    public $delimiter    = null;
    public $noDelimiter  = null;
    public $variadic     = Load::NOT_VARIADIC;
    public $count        = null;
    public $fullnspath   = '';
    public $absolute     = Load::NOT_ABSOLUTE;
    public $alias        = '';
    public $origin       = '';
    public $encoding     = '';
    public $block        = '';
    public $intval       = Intval::NO_VALUE;
    public $strval       = Strval::NO_VALUE;
    public $boolean      = Boolval::NO_VALUE;
    public $enclosing    = Load::NO_ENCLOSING;
    public $args_max     = '';
    public $args_min     = '';
    public $bracket      = Load::NOT_BRACKET;
    public $flexible     = Load::NOT_FLEXIBLE;
    public $close_tag    = Load::NO_CLOSING_TAG;
    public $propertyname = '';
    public $constant     = Load::NOT_CONSTANT_EXPRESSION;
    public $globalvar    = false;
    public $binaryString = Load::NOT_BINARY;
    public $isNull       = false;
    public $visibility   = '';
    public $final        = '';
    public $abstract     = false;
    public $readonly     = false;
    public $static       = '';
    public $noscream     = 0;
    public $trailing     = 0;
    public $isRead       = 0;
    public $isModified   = 0;
    public $use          = '';
    public $typehint     = 'one';
    public $isPhp        = 0;
    public $isExt        = 0;
    public $isStub       = 0;
    public $position     = 0;
    public Whitespace $ws ;

    abstract public function toArray(): array;

    abstract public function toGraphsonLine(int &$id): stdClass;

    abstract public function boolProperties(): array;

    abstract public function isA(array $atoms): bool;
}

?>
