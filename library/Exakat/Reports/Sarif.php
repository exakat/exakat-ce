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

namespace Exakat\Reports;

use Exakat\Exakat;

use Bartlett\Sarif\Definition\ArtifactLocation;
use Bartlett\Sarif\Definition\PropertyBag;
use Bartlett\Sarif\Definition\ReportingConfiguration;
use Bartlett\Sarif\Definition\Location;
use Bartlett\Sarif\Definition\PhysicalLocation;
use Bartlett\Sarif\Definition\Region;
use Bartlett\Sarif\Definition\Message;
use Bartlett\Sarif\Definition\MultiformatMessageString;
use Bartlett\Sarif\Definition\ReportingDescriptor;
use Bartlett\Sarif\Definition\Result;
use Bartlett\Sarif\Definition\Run;
use Bartlett\Sarif\Definition\Tool;
use Bartlett\Sarif\Definition\ToolComponent;
use Bartlett\Sarif\SarifLog;

class Sarif extends Reports {
    public const FILE_EXTENSION = 'sarif';
    public const FILE_FILENAME  = 'exakat';

    private array $analyzers        = array();

    public function _generate(array $analyzerList): string {
        $driver = new ToolComponent('Exakat');
        $driver->setInformationUri('https://www.exakat.io/');
        $driver->setFullName('Exakat ' . Exakat::VERSION);
        $driver->setSemanticVersion(Exakat::VERSION);
        $driver->setVersion(Exakat::VERSION);

        $tool = new Tool($driver);
        $run = new Run($tool);

        $precisionCache   = array();
        $severityCache    = array();
        $descriptionCache = array();

        $analysisResults = $this->dump->fetchAnalysers($analyzerList);
        foreach ($analysisResults->toArray() as $row) {
            // $sarif->addRule($row['analyzer'], $titleCache[$row['analyzer']], $descriptionCache[$row['analyzer']], $severityCache[$row['analyzer']], $precisionCache[$row['analyzer']]);
            if (!isset($titleCache[$row['analyzer']])) {
                $titleCache[$row['analyzer']]       = $this->docs->getDocs($row['analyzer'], 'name');
                $descriptionCache[$row['analyzer']] = $this->docs->getDocs($row['analyzer'], 'description');
                $severityCache[$row['analyzer']]    = $this->docs->getDocs($row['analyzer'], 'severity');
                $precisionCache[$row['analyzer']]   = $this->docs->getDocs($row['analyzer'], 'precision');
            }
            $this->count();

            if (!isset($this->analyzers[$row['analyzer']])) {
                $rule = new ReportingDescriptor($row['analyzer']);
                $rule->setShortDescription(
                    new MultiformatMessageString( $titleCache[$row['analyzer']] )
                );
                $rule->setHelp(
                    new MultiformatMessageString( $descriptionCache[$row['analyzer']] )
                );

                $propertyBag = new PropertyBag();
                $propertyBag->addProperty('precision', $precisionCache[$row['analyzer']]);
                $rule->setProperties($propertyBag);

                $reportingConf = new ReportingConfiguration();
                $propertyBag = new PropertyBag();
                $propertyBag->addProperty('level', $this->severity2level($severityCache[$row['analyzer']]));
                $reportingConf->setParameters($propertyBag);
                $rule->setDefaultConfiguration( $reportingConf );
                // @todo Help, HelpUri
                $driver->addRules(array($rule));

                $this->analyzers[$row['analyzer']] = count($this->analyzers);
            }

            $result = new Result(new Message((string) $row['fullcode'], md5((string) $row['fullcode'])));
            $result->setRuleId($row['analyzer']);
            $result->setRuleIndex($this->analyzers[$row['analyzer']]);
            $result->setLevel($this->severity2level($severityCache[$row['analyzer']]));
            $result->addPartialFingerprints(array('primaryLocationLineHash' => $this->fingerprints($row['file'], $row['line'], $row['fullcode'])));

            $location = new Location();
            $artifactLocation = new ArtifactLocation();
            $artifactLocation->setUri(ltrim($row['file'], '/'));
            $physicalLocation = new PhysicalLocation($artifactLocation);
            $region = new Region();
            $region->setStartLine($row['line']);
            $region->setEndLine($row['line']);
            $region->setStartColumn(1);
            $region->setEndColumn(200);
            $physicalLocation->setRegion($region);
            $location->setPhysicalLocation($physicalLocation);
            $result->addLocations(array($location));

            $run->addResults(array($result));

            $this->count();
        }

        $log = new SarifLog(array($run));

        return (string) $log;
    }

    private function severity2level(string $severity): string {
        // levels : none, note, warning, error
        switch ($severity) {
            case 'Critical':
            case 'Major':
                $level = 'error';
                break;

            case 'Minor':
                $level = 'warning';
                break;

            default:
            case 'Note':
            case 'None':
                $level = 'note';
                break;

        }

        return $level;
    }

    private function fingerprints(string $fileName, int $line, string $fullcode): string {
        return sha1($fileName . ':' . $line . ':' . $fullcode);
    }
}

?>