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

namespace Exakat;

use Exakat\Helpers\Timer;
use SplFileObject;

class Log {
    private string 		 	$name;
    private ?SplFileObject	$log;
    private Timer  			$timer;
    private string 		 	$first = '';

    public function __construct(string $name = '', string $dir = '.') {
        $this->name = $name;

        // If name is really long, keep 240 chars, and create a crc32 at the end
        if (strlen($this->name) > 250) {
            $this->name = substr($this->name, 0, 240) . '-' . crc32($this->name);
        }

        if (!file_exists("$dir/log/")) { return ; }
        if (!is_dir("$dir/log/")) { return ; }
        if (file_exists("$dir/log/{$this->name}.log")) {
            $this->log = new SplFileObject("$dir/log/{$this->name}.log", 'a');
            $this->first = "$this->name resuming on " . date('r');
        } else {
            $this->log = new SplFileObject("$dir/log/{$this->name}.log", 'w+');
            $this->first = "{$this->name} created on " . date('r');
        }
        if (!$this->log) {
            display("Couldn\'t create log in $dir/log/");
            $this->log = null;
        }

        $this->timer = new Timer();
    }

    public function __destruct() {
        if ($this->log === null) {
            return;
        }
        
        $this->timer->end();

        $this->log('Duration : ' . number_format($this->timer->duration(Timer::MS), 2));
        $this->log('Memory : ' . memory_get_usage(true));
        $this->log('Memory peak : ' . memory_get_peak_usage(true));
        $this->log("{$this->name} closed on " . date('r'));

        $this->log = null;
    }

    public function log(string $message): void {
        if ($this->log === null) { return; }

        if ($this->first !== '') {
            $this->log->fwrite("{$this->first}\n");
            $this->first = '';
        }

        $this->log->fwrite("$message\n");
    }
}

?>
