<?php
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

declare(strict_types = 1);

namespace Exakat\Exceptions;

use RuntimeException;

class WrongNumberOfArguments extends RuntimeException {
    public function __construct(string $method, int $obtained, int $expected, int $expected_max = -1) {
        if ($expected_max === -1) {
            parent::__construct("$method received $obtained arguments, and expected $expected.", 0, null);
        } else {
            parent::__construct("$method received $obtained arguments, and expected $expected to $expected_max.", 0, null);
        }
    }
}

?>