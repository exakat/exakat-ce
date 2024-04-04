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

namespace Exakat\Tasks\Helpers;

use Exakat\Config;

class BaselineStash {
    public const BASELINE_NONE   = 'none';
    public const BASELINE_ONE    = 'one';
    public const BASELINE_ALWAYS = 'always';

    public const NO_BASELINE     = '';

    public const STRATEGIES = array(self::BASELINE_NONE, self::BASELINE_ALWAYS);

    // 'none', 'always', '<Name>'
    private string $baselineStrategy = self::BASELINE_NONE;
    private string $baselineDir      = '';


    public function __construct(Config $config) {
        $this->baselineStrategy  = $config->baseline_set;
        $this->baselineDir       = $config->project_dir . '/baseline';

        if (!file_exists($this->baselineDir)) {
            if (!mkdir($this->baselineDir,0700)) {
                display('Could not create the baseline directory. No baseline will be saved.');
            }
        }
    }

    public function copyPrevious(string $previous, string $name = ''): string {
        if (!file_exists($previous)) {
            display("No previous audit found. Omitting baseline\n");

            return '';
        }

        if (!empty($name) && !in_array($name, self::STRATEGIES)) {
            // overwrite
            if (!copy($previous, $this->baselineDir . '/' . $name . '.sqlite')) {
                display('Could not save the baseline with the name ' . $name);
            }

            return '';
        }

        if ($this->baselineStrategy === self::BASELINE_NONE) {
            // Nothing to do
            return '';
        }

        if ($this->baselineStrategy === self::BASELINE_ALWAYS) {
            $baselines = glob("{$this->baselineDir}/dump-*.sqlite");
            if (empty($baselines)) {
                $last_id = 1;
            } else {
                usort($baselines, function (string $a, string $b) {
                    return $b <=> $a;
                } ); // simplistic reverse sorting
                $last = $baselines[0];
                $last_id = preg_match('/dump-(\d+)-/', $last, $r) ? (int) $r[1] : 1;
            }

            // Create a new
            // md5 is here for uuid purpose.
            $sqliteFilePrevious = $this->baselineDir . '/dump-' . ($last_id + 1) . '-' . substr(md5($this->baselineDir . ($last_id + 1)), 0, 7) . '.sqlite';
            if (!copy($previous, $sqliteFilePrevious)) {
                display('Could not save the baseline with the name ' . $name);
            }

            return $sqliteFilePrevious;
        }

        if ($this->baselineStrategy === self::BASELINE_ONE) {
            $sqliteFilePrevious = $this->baselineDir . '/dump-1.sqlite';
            if (!copy($previous, $sqliteFilePrevious)) {
                display('Could not save the baseline with the name ' . $name);
            }

            return $sqliteFilePrevious;
        }
    }

    public function getLastStash(): string {
        if ($this->baselineStrategy === self::BASELINE_ONE) {
            $sqliteFilePrevious = $this->baselineDir . '/dump-1.sqlite';
            return $sqliteFilePrevious;
        }

        if ($this->baselineStrategy === self::BASELINE_ALWAYS) {
            $baselines = glob("{$this->baselineDir}/dump-*.sqlite");
            if (empty($baselines)) {
                $last_id = 1;
            } else {
                usort($baselines, function (string $a, string $b) {
                    return $b <=> $a;
                } ); // simplistic reverse sorting
                $last = $baselines[0];
                $last_id = preg_match('/dump-(\d+)-/', $last, $r) ? (int) $r[1] : 1;
            }

            $sqliteFilePrevious = $this->baselineDir . '/dump-' . ($last_id + 1) . '-' . substr(md5($this->baselineDir . ($last_id + 1)), 0, 7) . '.sqlite';
            return $sqliteFilePrevious;
        }

        //BASELINE_NONE
        return '';
    }

    public function removeBaseline(string $id): void {
        $id = basename($id);
        if (file_exists("{$this->baselineDir}/$id.sqlite")) {
            display("Removing baseline '$id'\n");
            unlink("{$this->baselineDir}/$id.sqlite");

            return;
        }

        $baselines = glob("{$this->baselineDir}/dump-*-$id.sqlite");
        if (!empty($baselines) && count($baselines) === 1) {
            $baseline = basename($baselines[0], '.sqlite');
            display("Removing baseline '$baseline'\n");

            unlink($baselines[0]);

            return;
        }

        display("Could not find $id baseline\n");
    }

    public function getBaseline(): string {
        if ($this->baselineStrategy === self::BASELINE_NONE) {
            return self::NO_BASELINE;
        }

        if ($this->baselineStrategy === 'always') {
            $baselines = glob("{$this->baselineDir}//dump-*-*.sqlite");
            if (empty($baselines)) {
                return self::NO_BASELINE;
            }

            // Get the last one
            sort($baselines);
            return array_pop($baselines);
        }

        // full name in use
        if (file_exists("{$this->baselineDir}/{$this->baselineStrategy}.sqlite")) {
            return "{$this->baselineDir}/{$this->baselineStrategy}.sqlite";
        }

        // dump-xxx-AAAAAAA.sqlite name
        if (file_exists("{$this->baselineDir}/dump-\d+-{$this->baselineStrategy}.sqlite")) {
            return "{$this->baselineDir}/{$this->baselineStrategy}.sqlite";
        }

        return self::NO_BASELINE;
    }
}

?>
