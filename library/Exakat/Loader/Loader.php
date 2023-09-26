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


namespace Exakat\Loader;

use Exakat\Tasks\Helpers\Atom;
use Exakat\Tasks\Helpers\AtomInterface;
use Exakat\Exceptions\NoSuchLoader;
use Sqlite3;

abstract class Loader {
    public const LOADER_LIST = array('SplitGraphsonId',
                              'Collector',
                              'None',
                              );

    private array $loaderList = array();

    abstract protected function __construct(Sqlite3 $sqlite, Atom $id0, bool $withWs = AtomInterface::WITHOUT_WS);

    abstract public function finalize(array $relicat): bool;

    public function saveFiles(string $exakatDir, array $atoms, array $links): void {
    }

    public static function getInstance(string $loader, Sqlite3 $sqlite, Atom $id0, bool $withWs = AtomInterface::WITHOUT_WS): self {
        $className = "\Exakat\Loader\\$loader";
        if (!class_exists($className)) {
            throw new NoSuchLoader($loader, self::LOADER_LIST);
        }

        return new $className($sqlite, $id0, $withWs);
    }
}

?>
