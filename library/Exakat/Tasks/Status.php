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

use Exakat\Exakat;
use Exakat\Vcs\Vcs;
use Exakat\Vcs\None;
use Exakat\Reports\Reports;
use Exakat\Exceptions\NoSuchProject;

class Status extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    public const WITH_JSON    = 1;
    public const WITHOUT_JSON = 2;

    public function run(): void {
        $project = $this->config->project;

        if ($project->isDefault()) {
            $status = array();

            if (file_exists("{$this->config->tmp_dir}/Project.json")) {
                $json = file_get_contents("{$this->config->tmp_dir}/Project.json");
                if (empty($json)) {
                    $projectStatus = '';
                    $projectStep = '';
                } else {
                    $json = json_decode($json);
                    $projectStatus = $json->project;
                    $projectStep = $json->step;
                }

                $status = array('Running'  => 'Project',
                                'project'  => $projectStatus,
                                'step'     => $projectStep, );
            } else {
                $status['Running'] = 'idle';
            }

            $this->display($status, $this->config->json ? self::WITH_JSON : self::WITHOUT_JSON);
            return;
        }

        if (!file_exists($this->config->project_dir)) {
            throw new NoSuchProject((string) $project);
        }

        if ($this->datastore->getHash('exakat_version') === null) {
            $this->datastore->create();
            $this->datastore->addRow('hash', array('exakat_version'  => Exakat::VERSION,
                                                   'exakat_build'    => Exakat::BUILD,
                                                   'php_version'     => $this->config->phpversion,
                                                   'file_extensions' => json_encode($this->config->file_extensions),
                                                   'ignore_dirs'     => json_encode($this->config->ignore_dirs),
                                                   'include_dirs'    => json_encode($this->config->include_dirs),
                                                   'vcs_url'         => $this->config->project_url,
                                                   'project'         => (string) $this->config->project,
                                                ));
        }

        $status = array('project'          => (string) $project,
                        'files'            => (int) ($this->datastore->getHash('files')         ?? 0),
                        'filesIgnored'     => (int) ($this->datastore->getHash('filesIgnored')  ?? 0),
                        'loc'              => (int) ($this->datastore->getHash('loc')           ?? 0),
                        'loc_all'          => (int) ($this->datastore->getHash('locTotal')      ?? 0),
                        'tokens'           => (int) ($this->datastore->getHash('tokens')        ?? 0),
                        'vcs'              => $this->datastore->getHash('vcs_type')      ?? '',
                        'url'              => $this->datastore->getHash('vcs_url')       ?? '',
                        'branch'           => $this->datastore->getHash('vcs_branch')    ?? '',
                        'revision'         => $this->datastore->getHash('vcs_revision')  ?? '',
                        'php'              => $this->datastore->getHash('php_version')   ?? '',
                        'include_dirs'     => join(', ', json_decode($this->datastore->getHash('include_dirs') ?? '[]')),
                        'ignore_dirs'      => join(', ', json_decode($this->datastore->getHash('ignore_dirs')  ?? '[]')),
                        'file_extensions'  => join(', ', json_decode($this->datastore->getHash('file_extensions')  ?? '[]')),
                        );
        if (file_exists("{$this->config->tmp_dir}/Project.json")) {
            $text = file_get_contents("{$this->config->tmp_dir}/Project.json");
            if (empty($text)) {
                $inited = $this->datastore->getHash('inited');
                $status['status'] = empty($inited) ? 'Init phase' : 'Not running';
            } else {
                $json = json_decode($text);
                if ($json->project === $project) {
                    $status['status'] = $json->step;
                } else {
                    $inited = $this->datastore->getHash('inited');
                    $status['status'] = empty($inited) ? 'Init phase' : 'Not running';
                }
            }
        } else {
            $inited = $this->datastore->getHash('inited');
            $status['status'] = empty($inited) ? 'Init phase' : 'Not running';
        }

        if (($vcs = Vcs::getVcs($this->config)) instanceof None) {
            $status['hash']      = 'None';
            $status['updatable'] = 'N/A';
        } else {
            $status = array_merge($status, $vcs->getStatus());
        }

        $status['updatable'] = $status['updatable'] === true ? 'Yes' : 'No';

        // Check the logs
        $errors = $this->getErrors();
        if (!empty($errors)) {
            $status['errors'] = $errors;
        }

        // Status of progress
        // errors?

        $formats = array();
        foreach (Reports::$FORMATS as $format) {
            $a = $this->datastore->getHash($format);
            if (!empty($a)) {
                $formats[$format] = $a;
            }
        }
        // Always have formats, even if empty
        $status['formats'] = $formats;

        $this->display($status, $this->config->json ? self::WITH_JSON : self::WITHOUT_JSON);
    }

    private function display(array $status, int $json = self::WITH_JSON) : void {
        // Json publication
        if ($json === self::WITH_JSON) {
            print json_encode($status);
            return;
        }

        // commandline publication
        $text = '';
        $size = 0;
        foreach ($status as $k => $v) {
            $size = max($size, strlen($k));
        }

        foreach ($status as $field => $value) {
            if (is_array($value)) {
                $sub = str_pad($field, $size, ' ') . ' : ' . PHP_EOL;

                $sizea = 0;
                foreach ($value as $k => $v) {
                    $sizea = max($sizea, strlen($k));
                }
                foreach ($value as $k => $v) {
                    $sub .= '    ' . str_pad($k, $sizea, ' ') . ' : ' . $v . PHP_EOL;
                }
                $text .= PHP_EOL . $sub . PHP_EOL;
            } else {
                $text .= str_pad($field, $size, ' ') . ' : ' . $value . PHP_EOL;
            }
        }

        print $text;
    }

    private function getErrors(): array {
        $errors = array();

        // Init error
        $e = $this->datastore->getHash('init error');
        if (!empty($e)) {
            $errors['init error'] = $e;
            return $errors;
        }

        // Size error
        $e = $this->datastore->getHash('token error');
        if (!empty($e)) {
            $errors['init error'] = $e;
            return $errors;
        }

        return $errors;
    }
}

?>
