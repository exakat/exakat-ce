<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

class Whitespace {
    public $closing               = null;
    public $opening               = null;
    public $operator              = null;
    public $else                  = null;
    public $separators            = array();
    public $toblock               = null;
    public $as                    = null;
    public $toargs                = null;
    public $endargs               = null;
    public $totype                = null;

    public $toextends             = null;
    public $toextendsseparator    = null;
    public $toimplements          = null;
    public $toimplementsseparator = null;

    public $touse                 = null;
    public $touseseparators       = null;

    public $bodyseparator         = null;

    public $abstract              = null;
    public $final                 = null;
    public $variadic              = null;
    public $reference             = null;

    public $visibility            = null;
    public $static                = null;

    public function __construct(string $ws = '') {
        $this->closing = $ws;
    }

    public function toJson(): string {
        $json = (array) $this;
        $json = array_filter($json, function ($x) { return $x !== null; });
        if (empty($json['separators'])) {
            unset($json['separators']);
        }

        foreach($json as &$entry) {
            if (is_string($entry) && !mb_check_encoding($entry, 'UTF-8')) {
                $entry = utf8_encode($entry);
            } elseif (is_array($entry)) {
                foreach($entry as &$entry2) {
                    $entry2 = utf8_encode($entry2);
                }
                unset($entry2);
            }
        }
        unset($entry);

        $return = json_encode($json);
        if (json_last_error()) {
            print 'WS JSON error : ' . json_last_error_msg();
            print_r($json);
            die();
        }

        return json_encode($json);
    }
}

?>
