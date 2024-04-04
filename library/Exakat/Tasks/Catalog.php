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

namespace Exakat\Tasks;

use Exakat\Reports\Reports;
use Symfony\Component\Yaml\Yaml as Symfony_Yaml;
use Exakat\Exceptions\NoSuchReport;

class Catalog extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    public function run(): void {
        $data = array();

        // List of analysis
        $rulesets = $this->rulesets->listAllRulesets();
        sort($rulesets);
        $rulesets = array_map( function (string $x): string {
            if (str_contains($x, ' ')  ) {
                $x = '"' . $x . '"';
            }
            return $x;
        }, $rulesets);
        $data['rulesets'] = $rulesets;

        // List of reports
        $r = Reports::$FORMATS;
        $reports = array();
        foreach ($r as $report) {
            try {
                Reports::getInstance($report);
                $reports[] = $report;
            } catch (NoSuchReport $e) {
                // just ignore this
            }
        }
        sort($reports);
        $data['reports'] = $reports;

        // List of rules
        $rules = exakat('rulesets')->listAllAnalyzer();
        sort($rules);
        $data['rules'] = $rules;

        if ($this->config->json === true) {
            print json_encode($data);
        } elseif ($this->config->yaml === true) {
            print Symfony_Yaml::dump($data);
        } else {
            $display = '';

            foreach ($data as $section => $list) {
                $display .= count($list) . " $section : \n";
                $display .= '   ' . implode("\n   ", $list) . "\n";
            }

            print $display;
        }
    }
}

?>
