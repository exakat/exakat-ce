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


namespace Exakat\Exceptions;

use Exception;
use RuntimeException;

class MissingFile extends RuntimeException {
    private const MAX_MISSING_FILE_DISPLAYED = 3;

    public function __construct(array $missing = array(), int $code = 0, ?Exception $previous = null) {
        $c = count($missing);
        if ($c > self::MAX_MISSING_FILE_DISPLAYED) {
            $display = array_slice($missing, 0, self::MAX_MISSING_FILE_DISPLAYED);
            $displayNames = implode(', ', $display) . '...';
        } else {
            $displayNames = implode(', ', $missing);
        }
        parent::__construct($c . ' file' . ($c > 1 ? 's are' : ' is') . ' missing from the cache (' . $displayNames . '). Please, run "files" command to refresh the cache. ' . PHP_EOL . 'Aborting' . PHP_EOL, $code, $previous);
    }
}

?>