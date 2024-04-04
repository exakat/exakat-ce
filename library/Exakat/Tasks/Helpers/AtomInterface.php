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

namespace Exakat\Tasks\Helpers;

use stdClass;
use Exakat\Tasks\Load;

abstract class AtomInterface {
    public const WITHOUT_WS = false;
    public const WITH_WS	= true;

    public const NO_RANK	= null;
    public const NO_ATOM	= 'No Atom Set';

    public int     $id           	= 0;
    public string  $atom         	= self::NO_ATOM;
    public int|string $code;
    public int     $lccode    	    ;
    // dynamically generated, but only there for DSL Query
    public string  $fullcode     	= '';
    public int     $line         	= Load::NO_LINE;
    public string  $token        	= '';
    public ?int    $rank         	= self::NO_RANK; // Not 0
    public string  $rankName     	= '';
    public bool    $alternative  	= Load::NOT_ALTERNATIVE;
    public ?string $delimiter    	= null;
    public ?string $noDelimiter  	= null;
    public ?int    $count        	= null;
    public string  $fullnspath   	= '';
    public string  $alias        	= Load::NOT_ALIASED;
    public string  $origin       	= '';
    public string  $encoding     	= '';
    public string  $block        	= '';
    public ?int    $intval       	= Intval::NO_VALUE;
    public ?string $strval      	= Strval::NO_VALUE;
    public ?bool   $boolean      	= Boolval::NO_VALUE;
    public bool    $enclosing    	= Load::NO_ENCLOSING;
    public bool    $bracket      	= Load::NOT_BRACKET;
    public bool    $flexible     	= Load::NOT_FLEXIBLE;
    public bool    $close_tag    	= Load::NO_CLOSING_TAG;
    public ?string $propertyname 	= null;
    public bool    $constant     	= Load::NOT_CONSTANT_EXPRESSION;
    public string  $binaryString 	= Load::NOT_BINARY;
    public string  $visibility   	= '';  // none, public, private, protected, ''

    public ?int $args_max     = null;
    public ?int $args_min     = null;

    public bool $reference		= Load::NOT_REFERENCE;
    public bool $heredoc		= false;
    public bool $variadic		= Load::NOT_VARIADIC;
    public bool $absolute		= Load::NOT_ABSOLUTE;
    public bool|string $globalvar	= false;
    public bool $final			= false;
    public bool $isNull			= false;
    public bool $abstract		= false;
    public bool $readonly		= false;
    public bool $static			= false;
    public bool $noscream		= false;
    public bool $trailing		= false;
    public bool $isRead			= false;
    public bool $isModified		= false;
    public bool $isPhp			= false;
    public bool $isExt			= false;
    public bool $isStub			= false;
    public bool $isConst		= false;

    public string $use			= '';
    public ?string $typehint	= null;

    public Whitespace $ws;
    public int $eId;

    public function __construct(string $ws) {
        $this->ws   = new Whitespace($ws);
    }

    abstract public function toGraphsonLine(int &$id, bool $toSkip = self::WITHOUT_WS): stdClass;

    abstract public function boolProperties(): array;

    abstract public function isA(array $atoms): bool;
}

?>
