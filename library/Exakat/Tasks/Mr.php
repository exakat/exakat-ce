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

use Exakat\Exceptions\MissingGremlin;
use Exakat\Exceptions\InvalidProjectName;
use Exakat\Exceptions\NoCodeInProject;
use Exakat\Exceptions\NoSuchProject;
use Exakat\Exceptions\ProjectNeeded;

class Mr extends Tasks {
    public const CONCURENCE = self::NONE;

    public function run(): void {
        if ($this->config->project->isDefault()) {
            throw new ProjectNeeded();
        }

        if (!$this->config->project->validate()) {
            throw new InvalidProjectName($this->config->project->getError());
        }

        if ($this->config->gremlin === 'NoGremlin') {
            throw new MissingGremlin();
        }

        if (!file_exists($this->config->project_dir)) {
            throw new NoSuchProject((string) $this->config->project);
        }

        if (!file_exists($this->config->code_dir)) {
            throw new NoCodeInProject((string) $this->config->project);
        }

        if (!file_exists($this->config->project_dir . '/history')) {
            print $this->config->project_dir . '/history' . "\n";
            mkdir($this->config->project_dir . '/history', 0755);
        }

        display('Origin branch ' . $this->config->origin);
        shell_exec('cd ' . $this->config->project_dir . '/code; git checkout ' . $this->config->origin);

        $analyze = new Project(self::IS_SUBTASK);
        $analyze->run();
        unset($analyze);

        // @todo check for existing history
        rename($this->config->project_dir . '/dump.sqlite', $this->config->project_dir . '/history/' . $this->config->origin . '.sqlite');

        // @todo : check for correct execution
        shell_exec('cd ' . $this->config->project_dir . '/code; git checkout ' . $this->config->destination);

        display('Destination branch ' . $this->config->destination);
        $analyze = new Project(self::IS_SUBTASK);
        $analyze->run();
        unset($analyze);

        // @todo check for existing history storage
        rename($this->config->project_dir . '/dump.sqlite', $this->config->project_dir . '/history/' . $this->config->destination . '.sqlite');

        // compare files
        // rappor tde comparaison
    }
}

?>
