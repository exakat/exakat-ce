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


namespace Exakat\Analyzer\Functions;

use Exakat\Analyzer\Analyzer;
use Exakat\Stubs\Stubs;

class RedeclaredPhpFunction extends Analyzer {
    public function analyze(): void {
        $phpCore = new Stubs($this->config->dir_root . '/data/core/',
            $this->config->php_core ?? array(),
        );

        $phpExtensions = new Stubs($this->config->dir_root . '/data/extensions/',
            $this->config->php_extensions ?? array(),
        );

        $functions = array_merge($phpCore->getFunctionList(),
            $phpExtensions->getFunctionList(),
        );

        $this->atomIs('Function')
             ->fullnspathIs($functions);
        $this->prepareQuery();
    }
}

?>
