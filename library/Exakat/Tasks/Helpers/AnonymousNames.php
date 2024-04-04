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

use Exakat\Exceptions\LoadError;

class AnonymousNames {
    private int $anonymousNames = 0;

    public const A_CLASS          = 'class';
    public const A_FUNCTION       = 'function';
    public const A_ARROW_FUNCTION = 'arrowfunction';

    // No constructor

    public function getName(string $type): string {
        if (!in_array($type, array(self::A_CLASS, self::A_FUNCTION, self::A_ARROW_FUNCTION), \STRICT_COMPARISON)) {
            throw new LoadError('Classes, Functions and ArrowFunctions are the only anonymous');
        }

        ++$this->anonymousNames;
        return "$type@$this->anonymousNames";
    }
}

?>
