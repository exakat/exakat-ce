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

use Exakat\Exceptions\NoSuchProject;
use Exakat\Exceptions\ProjectNeeded;
use Exakat\Exceptions\NoSuchRuleset;
use Exakat\Exceptions\InvalidProjectName;
use Exakat\Exceptions\ProjectNotInited;
use Exakat\Exceptions\NoDump;
use Exakat\Exceptions\NoDumpYet;
use Exakat\Reports\Reports as Reports;
use Exakat\Tasks\Helpers\ReportConfig;
use Exakat\Helpers\Timer;
use Exakat\Dump\Dump;
use Exakat\Log;
use const STRICT_COMPARISON;

class Report extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    public function run(): void {
        if ($this->config->project->isDefault()) {
            throw new ProjectNeeded();
        }

        if (!$this->config->project->validate()) {
            throw new InvalidProjectName($this->config->project->getError());
        }

        if (!file_exists($this->config->project_dir)) {
            throw new NoSuchProject((string) $this->config->project);
        }

        if (!file_exists($this->config->datastore)) {
            throw new ProjectNotInited($this->config->project);
        }

        if (!file_exists($this->config->dump)) {
            throw new NoDump((string) $this->config->project);
        }

        $this->log = new Log ('reports',
            "{$this->config->projects_root}/projects/{$this->config->project}");

        if (empty($this->config->program)) {
            $rulesets = $this->config->project_rulesets;

            $unknown = array();
            foreach ($rulesets as $ruleset) {
                if ($ruleset === 'None') {
                    // this one is empty on purpose
                    continue;
                }
                if ( empty($this->rulesets->getRulesetsAnalyzers(array($ruleset) ) ) ) {
                    $unknown[] = $ruleset;
                }
            }
            if (!empty($unknown)) {
                throw new NoSuchRuleset(implode(', ', $unknown), $this->rulesets->getSuggestionRuleset($unknown));
            }
        } else {
            $rulesets = $this->config->program;
        }

        $dump = Dump::factory($this->config->dump, Dump::READ);
        $res = $dump->fetchAnalysersCounts(array('Project/Dump'));

        if ($res->toInt('count') !== 1) {
            throw new NoDumpYet($this->config->project);
        }

        $this->log->log(var_export($this->config->project_reports, true));
        foreach ($this->config->project_reports as $format) {
            $this->log->log('Building report ' . $format);
            $b = hrtime(true);

            $reportConfig = new ReportConfig($format, $this->config);
            $reportClass = $reportConfig->getFormatClass();

            $report = new $reportClass($reportConfig->getConfig());

            $this->format($report, $reportConfig);
            $e = hrtime(true);
            $this->log->log('Report time : ' . number_format(($e -$b) / 1000000, 2) . ' ms');
        }
    }

    private function format(Reports $report, ReportConfig $reportConfig): void {
        $timer = new Timer();

        if ($reportConfig->getFile() === Reports::STDOUT) {
            display("Building report for project {$this->config->project_name} to stdout, with report " . $reportConfig->getFormat() . "\n");
            $report->generate($this->config->project_dir, Reports::STDOUT);
        } elseif (empty($reportConfig->getFile())) {
            display("Building report for project {$this->config->project_name} in '" . $reportConfig->getFile() . "', with report " . $reportConfig->getFormat() . "\n");
            $report->generate($this->config->project_dir, $report::FILE_FILENAME);
        } else {
            // to files + extension
            $filename = basename($reportConfig->getFile());
            if (in_array($filename, array('.', '..'), STRICT_COMPARISON)) {
                $filename = $report::FILE_FILENAME;
            }
            display('Building report for project ' . $this->config->project . ' in "' . $reportConfig->getFile() . ($report::FILE_EXTENSION ? '.' . $report::FILE_EXTENSION : '') . "', with format " . $reportConfig->getFormat() . "\n");
            $report->generate( $this->config->project_dir, $filename);
        }
        display('Reported ' . $report->getCount() . ' messages in ' . $reportConfig->getFormat());

        $timer->end();

        display('Processing time : ' . number_format($timer->duration(), 2) . 's');

        $this->datastore->addRow('hash', array($reportConfig->getFormat() => $reportConfig->getFile() ));
        display('Done');
    }
}

?>
