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

use Exakat\Exceptions\NoStubDir;

class Stubs {
    private array  $stubs       = array();
    private string $stubsDir    = '';

    public function __construct(string $stubsDir	= '',
                                array $stubsFiles	= array()) {
        $files = $this->readFolder($stubsDir);

        $all = array_merge($files, $stubsFiles);
        foreach ($all as $file) {
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
                $this->stubs[basename($file, '.pdff')] = new PdffReader($file);
            }
            // @todo : Format manuel?
        }
    }

    private function readFolder(string $stubsDir): array {
        if (empty($stubsDir)) {
            return array();
        }

        if (!file_exists($stubsDir)) {
            return array();
        }

        if (!is_dir($stubsDir)) {
            return array();
        }

        $files = array();
        $dh = opendir($stubsDir);
        if (!$dh) {
            throw new NoStubDir($stubsbDir);
        }

        while (($file = readdir($dh)) !== false) {
            if ($file[0] === '.') {
                continue;
            }
            if (!str_contains($file, '.')) {
                continue;
            }
            $files[] = $file;
        }
        closedir($dh);

        return $files;
    }

    public function list(): array {
        return array_keys($this->stubs);
    }

    public function get(string $name): StubsInterface {
        if (!isset($this->stubs[$name])) {
            throw new \Exception('No such stub as ' . $name);
        }

        return $this->stubs[$name];
    }

    public function __call(string $name, array $args): array {
        assert(method_exists(StubsInterface::class, $name), "No such method as $name in stubs");

        if (empty($this->stubs)) {
            return array();
        }
        // Check on $name values
        $return = array();

        foreach ($this->stubs as $stub) {
            assert(method_exists($stub, $name), "No such method as $name for Definition file");
            $return[] = $stub->$name(...$args);
        }

        return array_merge(...$return);
    }
}

?>
