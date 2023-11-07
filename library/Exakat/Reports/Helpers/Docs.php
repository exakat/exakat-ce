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

namespace Exakat\Reports\Helpers;

use Exakat\Analyzer\Analyzer;
use Exakat\Autoload\AutoloadExt;
use Exakat\Autoload\AutoloadDev;

class Docs {
    private string      $pathToIni;
    private AutoloadExt $ext;
    private AutoloadDev $dev;

    private static array $docs = array();

    public function __construct(string $pathToIni, AutoloadExt $ext, AutoloadDev $dev) {
        $this->pathToIni = $pathToIni;
        $this->ext = $ext;
        $this->dev = $dev;
    }

    public function getDocs(string $analyzer, string $property = null): mixed {
        if (isset(self::$docs[$analyzer])) {
            if (isset(self::$docs[$analyzer][$property])) {
                return self::$docs[$analyzer][$property];
            } else {
                return self::$docs[$analyzer];
            }
        }

        // Move do doc loader class
        if (file_exists("{$this->pathToIni}/human/en/$analyzer.ini")) {
            $ini = parse_ini_file("{$this->pathToIni}/human/en/$analyzer.ini", \INI_PROCESS_SECTIONS);
        } elseif (($iniString = $this->ext->loadData("human/en/$analyzer.ini")) !== '') {
            $ini = parse_ini_string($iniString, \INI_PROCESS_SECTIONS);
        } elseif (($iniString = $this->dev->loadData("human/en/$analyzer.ini")) !== '') {
            $ini = parse_ini_string($iniString, \INI_PROCESS_SECTIONS);
        } elseif (($iniString = $this->dev->loadData("human/en/$analyzer.ini")) !== '') {
            $ini = parse_ini_string($iniString, \INI_PROCESS_SECTIONS);
        } else {
            assert(file_exists("{$this->pathToIni}/human/en/$analyzer.ini"), "No documentation for '$analyzer'.");
        }

        assert($ini !== null, "No readable documentation for '$analyzer'.");
        assert($ini['exakatSince'] !== null, "No exakatSince documentation for '$analyzer'.");

        $ini['parameter'] = array();
        $ranks = array_filter(array_keys($ini), function (string $s): int {
            return preg_match('/^parameter\d+$/', $s) ?: 0;
        });
        foreach ($ranks as $rank) {
            $ini['parameter'][] = $ini[$rank];
            unset($ini[$rank]);
        }

        if (empty($ini['severity'])) {
            $ini['severity'] = Analyzer::S_NONE;
        } else {
            $ini['severity'] = constant(Analyzer::class . '::' . $ini['severity']);
        }

        if (empty($ini['timetofix'])) {
            $ini['timetofix'] = Analyzer::T_NONE;
        } else {
            $ini['timetofix'] = constant(Analyzer::class . '::' . $ini['timetofix']);
        }

        if (empty($ini['phpversion'])) {
            $ini['phpversion'] = Analyzer::PHP_VERSION_ANY;
        }

        if (empty($ini['precision'])) {
            $ini['precision'] = Analyzer::P_NONE;
        } else {
            $ini['precision'] = constant(Analyzer::class . '::' . $ini['precision']);
        }

        self::$docs[$analyzer] = $ini;

        if (isset(self::$docs[$analyzer][$property])) {
            return self::$docs[$analyzer][$property];
        } else {
            return self::$docs[$analyzer];
        }
    }
}

?>