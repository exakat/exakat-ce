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

namespace Exakat\Tasks;

use Exakat\Config;
use Exakat\Exceptions\InvalidProjectName;
use Exakat\Exceptions\NoCodeInProject;
use Exakat\Exceptions\NoSuchProject;
use Exakat\Vcs\Vcs;

class Update extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    protected string $logname = self::LOG_NONE;

    public function run(): void {
        $project = $this->config->project;

        if (!$project->validate()) {
            throw new InvalidProjectName($project->getError());
        }

        if ($this->config->project->isDefault()) {
            $this->runDefault();
        } else {
            $this->runProject();
        }
    }

    private function runDefault(): void {
        if (!file_exists("{$this->config->projects_root}/projects")) {
            display("This installation has no projects directory. Aborting all update. Provide .exakat.ini to enable update in this folder.\n");
            return;
        }

        $paths = glob("{$this->config->projects_root}/projects/*");
        $projects = array_map('basename', $paths);
        $projects = array_diff($projects, array('test'));

        echo 'Updating ' . count($projects) . ' projects' . PHP_EOL;
        shuffle($projects);
        foreach ($projects as $project) {
            display("updating $project\n");

            $args = array(1 => 'update',
                          2 => '-p',
                          3 => $project,
            );
            $updateConfig = new Config($args);

            $this->update($updateConfig);
        }
    }

    private function runProject(): void {
        if (!file_exists($this->config->project_dir)) {
            throw new NoSuchProject((string) $this->config->project);
        }

        if (!is_dir($this->config->project_dir)) {
            throw new NoSuchProject((string) $this->config->project);
        }

        if (!file_exists($this->config->code_dir)) {
            throw new NoCodeInProject((string) $this->config->project);
        }

        // clean all previous sql caches
        $files = glob("{$this->config->project_dir}/.exakat/dump-*.php");
        display('Removing ' . count($files) . " dump-*.php files\n");
        foreach ($files as $file) {
            unlink($file);
        }

        $this->updateCode($this->config);
    }

    private function updateCode(Config $updateConfig): void {
        $vcs = Vcs::getVcs($updateConfig);

        display("Code update $updateConfig->project with " . $vcs->getName());
        $new = $vcs->update();
        if ($new === Vcs::NO_UPDATE) {
            display('No update available. Skipping');

            return;
        }

        display($vcs->getName() . " updated to $new");

        display('Running files');
        shell_exec($this->config->php . ' exakat files -p ' . $updateConfig->project);
    }
}
