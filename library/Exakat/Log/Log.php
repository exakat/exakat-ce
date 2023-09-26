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

namespace Exakat\Log;

use SplFileObject;

class Log {
    private ?SplFileObject $file = null;
    private string $name = '';

    public function __construct(string $name = '') {
        $this->name = $name;

        // If name is really long, keep 240 chars, and create a crc32 at the end
        if (strlen($this->name) > 250) {
            $this->name = substr($this->name, 0, 240) . '-' . crc32($this->name);
        }

        $config = exakat('config');
        $dir = $config->project_dir;

        if (!file_exists("$dir/log/")) {
            return ;
        }

        if (!is_dir("$dir/log/")) {
            return ;
        }

        if (file_exists("$dir/log/{$this->name}.log")) {
            $this->file = new SplFileObject("$dir/log/{$this->name}.log", 'a');
        } else {
            $this->file = new SplFileObject("$dir/log/{$this->name}.log", 'w+');
        }

        if (!$this->file) {
            display("Couldn\'t create log file in $dir/log/");
            $this->file = null;
        }
    }


    public function log(string $message): void {
        if ($this->file === null) {
            return;
        }

        $this->file->fwrite($message . PHP_EOL);
    }
}

?>
