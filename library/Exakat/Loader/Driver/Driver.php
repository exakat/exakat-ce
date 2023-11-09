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


namespace Exakat\Loader\Driver;

use SplFileObject;
use Exakat\Graph\Graph;

abstract class Driver {
    public const DEFAULT = 'Serial';
    public const DRIVERS = array('Serial', 'Parallel');

    protected const LOAD_CHUNK          = 100000;
    protected const LOAD_CHUNK_LINK     = 200000;
    protected const LOAD_CHUNK_PROPERTY = 100000;

    protected array $info = array();

    abstract public function saveNodes(?string $row = self::FINISH): void;

    abstract public function savePropertiesGremlin(string $attribute, array $properties): void;

    abstract public function finish(): void;

    abstract public function saveLinkGremlin(array $links): void;

    public static function getInstance(string $driver, string $tmpPath, string $installationPath, Graph $graphdb, SplFileObject $log, int $max_parallel = 0): self {
        $class = __NAMESPACE__ . '\\' . $driver;

        if ($max_parallel <= 0) {
            // default to serial
            $max_parallel = 1;
        } elseif ($max_parallel > 100) {
            $max_parallel = 100;
        }


        if ($max_parallel == 1) {
            // Fallback to 1 in case of 1 paralell.
            display('Loading with Serial (Fallback)');
            return new Serial($tmpPath, $installationPath, $graphdb, $log);
        } elseif (class_exists($class)) {
            display('Loading with '.$class);
            return new $class($tmpPath, $installationPath, $graphdb, $log, $max_parallel);
        } else {
            display('Loading with Serial (Default)');
            return new Serial($tmpPath, $installationPath, $graphdb, $log);
        }
    }

    public function getName(): string {
        return get_class($this);
    }

    public function getInfo(): array {
        return $this->info;
    }
}

?>
