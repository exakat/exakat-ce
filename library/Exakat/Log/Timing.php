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

namespace Exakat\Log;

class Timing extends Log {
    private float $start;  // start of the whole timer
    private float $begin;  // milestone
    private float $end;

    public function __construct(string $name = '') {
        assert(!empty($name), 'Cannot use an empty-named timing log');
        parent::__construct($name);

        $this->begin = microtime(\TIME_AS_NUMBER);
        $this->start = $this->begin;
    }


    public function log(string $message): void {
        $this->end = microtime(\TIME_AS_NUMBER);

        parent::log($message . "\t" . ($this->end - $this->begin) . "\t" . ($this->end - $this->start));
        $this->begin = $this->end;
    }
}

?>
