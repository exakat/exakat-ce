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

namespace Exakat\Reports;

use Exakat\Reports\Helpers\Docs;
use Exakat\Analyzer\Rulesets;
use Exakat\Dump\Dump;
use Exakat\Datastore;
use Exakat\Config;
use Exakat\Exceptions\NoSuchReport;

abstract class Reports {
    public const STDOUT = 'stdout';
    public const INLINE = 'inline';

    public const NOT_RUN      = 'Not Run';
    public const YES          = 'Yes';
    public const NO           = 'No';
    public const INCOMPATIBLE = 'Incompatible';

    public static $FORMATS        = array('Ambassador', 'Ambassadornomenu', 'Drillinstructor', 'Top10', 'Diplomat',
                                          'Text', 'Xml', 'Uml', 'Yaml', 'Plantuml', 'None', 'Simplehtml', 'Owasp', 'Perfile', 'Beautycanon',
                                          'Phpconfiguration', 'Phpcompilation', 'Favorites', 'Manual',
                                          'Inventories', 'Clustergrammer', 'Filedependencies', 'Filedependencieshtml', 'Classdependencies', 'Stubs', 'StubsJson',
                                          'Radwellcode', 'Grade', 'Scrutinizer', 'Codesniffer', 'Phpcsfixer',
                                          'Facetedjson', 'Json', 'Onepagejson', 'Marmelab', 'Simpletable', 'Exakatyaml',
                                          'Codeflower', 'Dependencywheel', 'Phpcity', 'Sarb',
                                          'Exakatvendors', 'Topology', 'CallGraph',
                                          'Migration73', 'Migration74', 'Migration80', 'Migration81', 'Migration82', 'Migration83',
                                          'Meters', 'Perrule',
                                          'CompatibilityPHP56', 'CompatibilityPHP74', 'CompatibilityPHP80', 'CompatibilityPHP81', 'CompatibilityPHP82', 'CompatibilityPHP83',
                                          'Compatibility',
                                          'Unused', 'History',
                                          'NoOneLiners', 'Test',
                                          //'DailyTodo',
                                          );

    protected array $themesToShow = array('CompatibilityPHP56', //'CompatibilityPHP53', 'CompatibilityPHP54', 'CompatibilityPHP55',
                                          'CompatibilityPHP70', 'CompatibilityPHP71', 'CompatibilityPHP72', 'CompatibilityPHP73',
                                          'CompatibilityPHP74',
                                          'CompatibilityPHP80',
                                          'CompatibilityPHP81',
                                          'Dead code', 'Security', 'Analyze', 'Inventories',
                                          'Dump',
                                          );

    private int 			$count = 0;

    protected array|string 	$themesList = array();      // cache for themes list in SQLITE
    protected Config 		$config;
    protected Docs 			$docs;

    protected Dump $dump;

    protected Datastore $datastore;
    protected Rulesets $rulesets;

    protected array $options   = array();

    public function __construct() {
        $this->config    = exakat('config');
        $format = explode('\\', static::class);
        $format = array_pop($format);
        // warning, this is case sensitive. Options should not be.
        // warning, options for reports (and others) are simply based on the report name, and may end in conflict with other names
        $this->options   = $this->config->$format ?? array();
        $this->docs      = exakat('docs');

        $this->rulesets  = exakat('rulesets');

        if (file_exists($this->config->dump)) {
            $this->dump      = Dump::factory($this->config->dump);

            // Default analyzers
            $analyzers = array_merge($this->rulesets->getRulesetsAnalyzers($this->config->project_results ?? array()),
                array_keys($this->config->rulesets));
            $this->themesList = makeList($analyzers);
        }
    }

    protected function _generate(array $analyzerList): string {
        return '';
    }

    public static function getReportClass(string $report): string {
        $report = ucfirst(strtolower($report));
        return "\\Exakat\\Reports\\$report";
    }

    public function generate(string $folder, string $name= 'table'): string {
        if (empty($name)) {
            // FILE_FILENAME is defined in the children class
            $name = static::FILE_FILENAME;
        }

        $rulesets = $this->config->project_rulesets;
        if (!empty($this->config->program)) {
            $list = makeArray($this->config->program);
        } elseif (!empty($rulesets)) {
            if ($missing = $this->checkMissingRulesets()) {
                print "Can't produce " . static::class . ' format. There are ' . count($missing) . ' missing rulesets : ' . implode(', ', $missing) . ".\n";
                return '';
            }

            $list = $this->rulesets->getRulesetsAnalyzers($rulesets);
        } else {
            $list = $this->rulesets->getRulesetsAnalyzers($this->themesToShow);
        }

        $final = $this->_generate($list);

        if ($name === self::STDOUT) {
            echo $final;
            return '';
        } elseif ($name === self::INLINE) {
            return $final;
        } else {
            file_put_contents("$folder/$name." . static::FILE_EXTENSION, $final);
            return '';
        }
    }

    protected function count(int $step = 1): void {
        $this->count += $step;
    }

    public function getCount(): int {
        return $this->count;
    }

    public function dependsOnAnalysis(): array {
        return array();
    }

    public function checkMissingRulesets(): array {
        $required = $this->dependsOnAnalysis();

        if (empty($required)) {
            return $required;
        }

        $available = $this->dump->fetchTable('themas')->toList('thema');

        if (empty($available)) {
            // Nothing found.
            return $required;
        }

        return array_diff($required, $available);
    }

    public static function getInstance(string $report): self {
        $class = "\Exakat\Reports\\$report";

        if (!class_exists($class)) {
            throw new NoSuchReport($class);
        }

        return new $class();
    }

    public static function getSuggestion(string $report): array {
        return array_filter(self::$FORMATS, function (string $c) use ($report): bool {
            $l = levenshtein($c, $report);
            if ($l < 6) {
                return true;
            }
            return false;
        });
    }
}

?>