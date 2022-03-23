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

namespace Exakat\Stubs;

class Stubs {
    private array  $stubs       = array();
    private string $stubsDir    = '';

    public function __construct(string $stubsDir   = '',
                                array $stubsFiles = array()) {
        $this->stubsDir = $stubsDir;

        if (empty($stubsDir)) {
            $files = array();
        } else {
            $files = glob($stubsDir . '*');
        }

        $all = array_merge($files, $stubsFiles);
        foreach($all as $file) {
            if ($file[0] !== '/') {
                $file = $stubsDir . '/' . $file;
            }

            if (is_dir($file)) {
                continue;
            }
            if (!file_exists($file)) {
                continue;
            }

            if (substr($file, -5) === '.json' ) {
                $this->stubs[] = new StubJson($file);
            } elseif (substr($file, -5) === '.pdff' ) {
                $this->stubs[] = new PdffReader($file);
            }
            // @todo : Format manuel?
        }
    }

    public function __call(string $name, array $args): array {
        assert(method_exists(StubsInterface::class, $name), "No such method as $name in stubs");

        if (empty($this->stubs)) {
            return array();
        }
        // Check on $name values
        $return = array();

        foreach($this->stubs as $stub) {
            assert(method_exists($stub, $name), "No such method as $name for Definition file");
            $return[] = $stub->$name();
        }

        return array_merge(...$return);
    }
}

?>
