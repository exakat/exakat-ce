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

namespace Exakat\Autoload;


class AutoloadExt implements Autoloader {
    public const LOAD_ALL = 'all';

    private array $pharList   = array();
    private array $extensions = array();

    public function __construct(string $path) {
        if (!extension_loaded('phar')) {
            // Ignoring it all
            return;
        }
        $list = glob("$path/*.phar", GLOB_NOSORT);

        foreach ($list as $phar) {
            $this->pharList[basename($phar, '.phar')] = $phar;
        }

        // Add a list of check on the phars
        // Could we autoload everything ?
    }

    public function autoload(string $name): void {
        $file = str_replace(array('Exakat\\', '\\'), array('', DIRECTORY_SEPARATOR), $name) . '.php';

        foreach ($this->pharList as $phar) {
            $fullPath = "phar://$phar/$file";
            if (file_exists($fullPath)) {
                include $fullPath;
                return;
            }
        }
    }

    public function registerAutoload(): void {
        spl_autoload_register($this->autoload(...));
    }

    private function checkDependencies(): void {
        // Report missing extensions, but don't prevent them (some rules may still work, others will be ignored)
        foreach ($this->extensions as $name => $extension) {
            $diff = array_diff($extension->dependsOnExtensions(), array_keys($this->pharList));
            if (!empty($diff)) {
                // This is displayed for extensions and also for their dependencies, leading to repetition.
                display("$name extension requires the following missing extension : " . implode(', ', $diff) . "\nProcessing may be impacted.\nDownload the missing extensions with the 'extension' command.\n");
            }
        }
    }

    public function getPharList(): array {
        return array_map('basename', $this->pharList);
    }

    public function getRulesets(): array {
        $return = array();

        foreach ($this->pharList as $name => $phar) {
            $fullPath = "phar://$phar/Exakat/Analyzer/analyzers.ini";

            if (!file_exists($fullPath)) {
                $return[] = array();
                continue;
            }
            $ini = parse_ini_file($fullPath);
            unset($ini['All']); // And other pre-defined themes ?

            $return[$name] = array_keys($ini);
        }

        return $return;
    }

    public function getAnalyzers(string $theme = 'All'): array {
        $return = array();

        foreach ($this->pharList as $name => $phar) {
            $fullPath = "phar://$phar/Exakat/Analyzer/analyzers.ini";

            if (!file_exists($fullPath)) {
                $return[] = array();
                continue;
            }
            $ini = parse_ini_file($fullPath);

            // @todo : throw here?

            $return[$name] = $ini[$theme] ?? array();
        }

        return $return;
    }

    public function getAllAnalyzers(): array {
        $return = array();

        foreach ($this->pharList as $name => $phar) {
            $fullPath = "phar://$phar/Analyzer/analyzers.ini";

            if (!file_exists($fullPath)) {
                display("Missing analyzers.ini in $name\n");
                $return[] = array();
                continue;
            }
            $ini = parse_ini_file($fullPath);

            $return[$name] = $ini;
        }

        return $return;
    }

    public function loadIni(string $name, ?string $libel = self::LOAD_ALL): array {
        $return = array();

        foreach ($this->pharList as $phar) {
            $fullPath = "phar://$phar/data/$name";

            if (!file_exists($fullPath)) {
                continue;
            }

            $ini = parse_ini_file($fullPath, \INI_PROCESS_SECTIONS);
            if (empty($ini)) {
                continue;
            }

            if ($libel === self::LOAD_ALL) {
                $return[] = $ini;
            } else {
                $return[] = $ini[$libel];
            }
        }

        if (empty($return)) {
            return array();
        }

        return array_merge(...$return);
    }

    public function loadJson(string $name, string $libel = self::LOAD_ALL): array {
        $return = array(array());

        foreach ($this->pharList as $phar) {
            $fullPath = "phar://$phar/data/$name";

            if (!file_exists($fullPath)) {
                continue;
            }

            $json = file_get_contents($fullPath);
            if (empty($json)) {
                continue;
            }

            $data = json_decode($json, \JSON_ASSOCIATIVE);

            if (json_last_error() !== \JSON_ERROR_NONE) {
                continue;
            }
            if (empty($data)) {
                continue;
            }

            if ($libel === self::LOAD_ALL) {
                $return[] = array_column($data, $libel);
            } else {
                $return[] = $data;
            }
        }

        return array_merge(...$return);
    }

    public function loadData(string $path): string {
        $return = array();
        foreach ($this->pharList as $phar) {
            $fullPath = "phar://$phar/$path";

            if (file_exists($fullPath)) {
                $return[] = file_get_contents($fullPath);
            }
        }

        return implode('', $return);
    }

    public function fileExists(string $path): bool {
        foreach ($this->pharList as $phar) {
            $fullPath = "phar://$phar/$path";

            if (file_exists($fullPath)) {
                return true;
            }
        }

        return false;
    }

    public function copyFile(string $path, string $to): void {
        foreach ($this->pharList as $phar) {
            $fullPath = "phar://$phar/$path";

            if (file_exists($fullPath)) {
                copy($fullPath, $to);
            }
        }
    }
}

?>