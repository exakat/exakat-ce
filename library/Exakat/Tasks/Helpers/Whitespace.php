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

class Whitespace {
    public string $closing              ;
    public string $opening              ;
    public string $operator             ;
    public string $else                 ;
    public array  $separators           = array();
    public string $toblock              ;
    public string $as                   ;
    public string $toargs               ;
    public string $endargs              ;
    public string $totype               ;
    public string $ellipsis             ;
    public string $noscream             ;
    public string $readonly             ;

    public string $init                 ;

    public string $toextends            ;
    public array  $toextendsseparator    = array();
    public string $toimplements         ;
    public array  $toimplementsseparator = array();

    public string $touse                ;
    public array  $touseseparators       = array();

    public array  $bodyseparator         = array();

    public string $abstract             ;
    public bool|string $final           ;
    public string $variadic             ;
    public string $reference            ;

    public string $visibility           ;
    public string $static               ;

    public function __construct(string $ws = '') {
        $this->closing = $ws;
    }

    public function toJson(): string {
        $json = (array) $this;
        $json = array_filter($json, function (mixed $x): bool {
            return $x !== null;
        });
        if (empty($json['separators'])) {
            unset($json['separators']);
        }

        foreach ($json as &$entry) {
            if (is_string($entry) && !mb_check_encoding($entry, 'UTF-8')) {
                $entry =  mb_convert_encoding($entry, 'UTF-8', 'ISO-8859-1');
            } elseif (is_array($entry)) {
                foreach ($entry as &$entry2) {
                    $entry2 = mb_convert_encoding($entry2, 'UTF-8', 'ISO-8859-1');
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

        return $return;
    }
}

?>
