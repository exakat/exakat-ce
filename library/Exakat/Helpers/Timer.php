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



namespace Exakat\Helpers;

/*

This replaces  
 $begin = microtime(true);
 // ...
 $end = microtime(true);

 

*/

class Timer {
    private const UNSET = -1;

    public const UNIT = 1;
    public const S      = 1;  // for Seconds
    public const MS     = 1000;
    public const MICROS = 1000000;

    private float $begin = self::UNSET;
    private float $end   = self::UNSET;

    public function __construct() {
        $this->begin = microtime(\TIME_AS_NUMBER);
    }

    public function destruct() {
        assert($this->end !== self::UNSET, "A timer was set but not used\n");
    }

    public function end(): void {
        $this->end = microtime(\TIME_AS_NUMBER);
    }

    public function duration(int $unit = self::UNIT): float {
        assert(in_array($unit, array(self::S, self::MS, self::MICROS)), "Timer unit doesn't exists : $unit");

        return ($this->end - $this->begin) * $unit;
    }
}

?>