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

namespace Exakat\Configsource;

use Exakat\Project;

class Webconfig extends Config {
    private array $parameters = array();

    private const COMMANDS = array(
        'version' => 1,
        'doctor' => 2,
        'project' => 3,
        // @todo finish the list
    );

    public function __construct() {
        $this->config['command'] = '<no-command>';
        $this->config['project'] = new Project();  // Default to no object
    }

    public function setArgs(array $args = array()): void {
        $this->parameters = $args;
    }

    public function loadConfig(Project $project): ?string {
        $command = $this->parameters['command'] ?? '';
        if (!isset(self::COMMANDS[$command])) {
            $command = '';
        }
        $this->config['command'] = $command;

        $this->config['verbose'] = isset($this->parameters['v']);

        if (isset($this->parameters['p'])) {
            $this->config['project'] = new Project((string) $this->parameters['p']);
        }

        return 'webconfig';
    }
}

?>