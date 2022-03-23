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

class Encoding extends Plugin {
    public function run(Atom $atom, array $extras): void {
        if (!function_exists('mb_detect_encoding')) {
            return;
        }

        switch ($atom->atom) {
            case 'Identifier' :
                $atom->encoding = mb_detect_encoding($atom->noDelimiter);
                if ($atom->encoding === 'UTF-8') {
                    $blocks = unicode_blocks($atom->noDelimiter);
                    $atom->block = array_keys($blocks)[0] ?? '';
                }
                break;

            case 'String' :
                // Case of a Quoted string " $a "
                if (!empty($extras)) {
                    $atom->encoding    = 'none';
                    $atom->block       = 'none';
                    break;
                }

                $atom->encoding = mb_detect_encoding($atom->noDelimiter);
                if ($atom->encoding === 'UTF-8') {
                    $blocks = unicode_blocks($atom->noDelimiter);
                    $atom->block = array_keys($blocks)[0] ?? '';
                }
                break;

        default :
            // Nothing, really
        }
    }
}

?>
