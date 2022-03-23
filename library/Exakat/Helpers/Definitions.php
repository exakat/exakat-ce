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

use Exakat\Config;

class Definitions {
    public const CONSTANTS         = 'constants';
    public const FUNCTIONS         = 'functions';
    public const CLASSES           = 'classes';
    public const TRAITS            = 'traits';
    public const INTERFACES        = 'interfaces';
    public const STATIC_CONSTANTS  = 'staticConstants';
    public const STATIC_PROPERTIES = 'staticProperties';
    public const STATIC_METHODS    = 'staticMethods';
    public const PROPERTIES        = 'properties';
    public const METHODS           = 'methods';

    private $ini     = array();
    private $isValid = true;

    public function __construct(Config $config, string $path) {
        if (file_exists($config->dir_root . '/data/' . $path . '.ini')) {
            $ini = parse_ini_file($config->dir_root . '/data/' . $path . '.ini');

            if ($ini === null) {
                $this->isValid = false;
            }
            $this->ini = $ini;
        } elseif (file_exists($config->dir_root . '/data/' . $path . '.json')) {
            $ini = json_decode(file_get_contents($config->dir_root . '/data/' . $path . '.json'), \JSON_ASSOCIATIVE);

            if ($ini === null) {
                $this->isValid = false;
            }
            $this->ini = $ini;
        } else {
            display( "No such definitions for '$path'\n");
            $this->isValid = false;
        }
    }

    public function isValid(): bool {
        return $this->isValid;
    }

    public function get(string $what): array {
        return $this->ini[$what] ?? array();
    }
}
?>
