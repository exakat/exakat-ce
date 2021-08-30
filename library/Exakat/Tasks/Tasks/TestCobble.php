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

namespace Exakat\Tasks;

use Exakat\Config;
use Exakat\Exceptions\NoSuchFile;
use Exakat\Exceptions\NoSuchDir;
use Exakat\Exceptions\NoSuchAnalyzer;

class TestCobble extends Tasks {
    public const CONCURENCE = self::NONE;

    public function run(): void {
        // Check for requested file
        if (!empty($this->config->filename) && !file_exists($this->config->filename[0])) {
            throw new NoSuchFile($this->config->filename[0]);
        } elseif (!empty($this->config->dirname) && !file_exists($this->config->dirname)) {
            throw new NoSuchDir($this->config->filename);
        }

        // Check for requested analyze
        $analyzerName = $this->config->program[0];
        /*
        if (!$this->rulesets->getClass($analyzerName)) {
            throw new NoSuchAnalyzer($analyzerName, $this->rulesets);
        }
        */

        display("Cleaning DB\n");
        $args = array ( 1 => 'cleandb',
                        2 => '-p',
                        3 => 'test',
                        4 => '-Q',
                        );
        $configThema = new Config($args);

        $analyze = new CleanDb(self::IS_SUBTASK);
        $analyze->setConfig($configThema);
        $analyze->run();

        display("Cleaning project\n");
        $clean = new Clean(self::IS_SUBTASK);
        $clean->run();

        $analyze = new Cobble(self::IS_SUBTASK);
        $analyze->run();
        unset($analyze);

        display("Analyzed project\n");
    }
}

?>