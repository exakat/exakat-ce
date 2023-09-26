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

use Exakat\Analyzer\Rulesets;
use Exakat\Data\Methods;
use Exakat\Exceptions\NoPhpBinary;
use Exakat\Graph\Graph;
use Exakat\Reports\Helpers\Docs;
use Exakat\Stubs\Stubs;
use Exakat\Config;

class Container {
    private bool $verbose    = false;

    private Config     	$config			;
    private Graph      	$graphdb		;
    private Datastore  	$datastore		;
    private Docs		$docs			;
    private Methods    	$methods		;
    private Rulesets	$rulesets		;
    private Phpexec		$php			;
    private array		$inited			= array();

    public function init(array $argv = array()): void {
        $this->config = new Config($argv);

        $this->verbose = $this->config->verbose;
    }

    public function __get(string $what) : mixed {
        assert(property_exists($this, $what), "No such property in the container : '$what'\n");

        if (!isset($this->$what)) {
            $this->$what();
            $this->inited[$what] = 1;
        }

        return $this->$what;
    }

    private function graphdb(): void {
        $this->graphdb    = Graph::getConnexion($this->config, $this->config->gremlin);
        $this->graphdb->init();
    }

    private function datastore(): void {
        $this->datastore  = new Datastore($this->config);
    }

    private function methods(): void {
        $this->methods    = new Methods($this->config);
    }

    private function docs(): void {
        $this->docs = new Docs($this->config->dir_root,
                               $this->config->ext,
                               $this->config->dev
                               );
    }

    private function rulesets(): void {
        $this->rulesets = new Rulesets("{$this->config->dir_root}/data/analyzers.sqlite",
                                       $this->config->dev,
                                       $this->config->rulesets,
                                       $this->config->ignore_rules
                                       );
    }

    private function php(): void {
        $phpVersion = 'php' . str_replace('.', '', $this->config->phpversion);
        if (empty($this->config->{$phpVersion})) {
            throw new NoPhpBinary("No php binary configured for version {$this->config->phpversion}. Update config/exakat.ini or change the running version.");
        }
        $this->php = new Phpexec($this->config->phpversion, $this->config->{$phpVersion});
    }
}

?>
