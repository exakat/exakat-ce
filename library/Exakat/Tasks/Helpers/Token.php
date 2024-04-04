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

use ArrayAccess;

class Token implements ArrayAccess {
    public string|int $token;
    public string $code;
    public int $line;
    public int $position = 0;
    public string $ws;

    private const OFFSETS = array(
        0 => 'token',
        1 => 'code',
        2 => 'line',
        3 => 'position',
        4 => 'ws',
    );

    public function __construct(string|int $token, string $code, int $line, int $position, string &$ws) {
        $this->token     = $token;
        $this->code      = $code;
        $this->line      = $line;
        $this->position  = $position;
        $this->ws        = $ws;
    }

    public function toWs(): string {
        return $this->code . $this->ws;
    }

    public function addWs(string $ws): void {
        $this->ws .= $ws;
    }

    public function offsetExists(mixed $offset): bool {
        return in_array($offset, array_keys(self::OFFSETS));
    }

    public function offsetGet(mixed $offset): mixed {
        if (!isset(self::OFFSETS[$offset])) {
            debug_print_backtrace();
            die('No such offset as ' . $offset);
        }
        $property = self::OFFSETS[$offset];

        return $this->$property;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        debug_print_backtrace();
        print $offset;
        die(__METHOD__);
        // nothing
    }

    public function offsetUnset(mixed $offset): void {
        die(__METHOD__);
        // nothing
    }
}

?>
