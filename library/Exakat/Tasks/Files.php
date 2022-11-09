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

use Exakat\Phpexec;
use Exakat\Config;
use Exakat\Exceptions\NoCodeInProject;
use Exakat\Exceptions\NoSuchProject;
use Exakat\Exceptions\ProjectNeeded;
use Exakat\Fileset\{All, Filenames, FileExtensions, IgnoreDirs};

class Files extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    private string $tmpFileName = '';

    public function run(): void {
        if ($this->config->project->isDefault()) {
            throw new ProjectNeeded();
        }

        $stats = array();
        foreach (Config::PHP_VERSIONS as $version) {
            $stats["notCompilable$version"] = 'N/C';
        }

        if ($this->config->inside_code === Config::INSIDE_CODE) {
            // OK
        } elseif (!empty($this->config->filename)) {
            // OK
        } elseif ($this->config->project === 'default') {
            throw new ProjectNeeded();
        } elseif (!file_exists($this->config->project_dir)) {
            throw new NoSuchProject($this->config->project);
        } elseif (!file_exists($this->config->code_dir)) {
            throw new NoCodeInProject($this->config->project);
        }

        $this->checkComposer($this->config->code_dir);
        $this->checkLicence($this->config->code_dir);

        $ignoredFiles = array();
        $files = array();

        display("Searching for files \n");
        $set = new All($this->config->code_dir);
        $set->addFilter(new Filenames($this->config->dir_root));
        $set->addFilter(new FileExtensions($this->config->file_extensions));
        $set->addFilter(new IgnoreDirs($this->config->ignore_dirs, $this->config->include_dirs));

        $files = $set->getFiles();
        $ignoredFiles = $set->getIgnored();
        display('Found ' . count($files) . " files.\n");

        $filesRows = array();
        $hashes = array();
        $duplicates = 0;
        foreach ($files as $id => $file) {
            $fnv132 = hash_file('fnv132', $this->config->code_dir . $file);
            if (isset($hashes[$fnv132])) {
                $ignoredFiles[$file] = "Duplicate ({$hashes[$fnv132]})";
                ++$duplicates;
                unset($files[$id]);
                continue;
            } else {
                $hashes[$fnv132] = $file;
            }
            $modifications = 0;
            $filesRows[] = compact('file', 'fnv132', 'modifications');
        }
        display("Removed $duplicates duplicates files\n");

        if (empty($files)) {
            $this->datastore->addRow('hash', array('files'           => 0,
                                                   'filesIgnored'    => count($ignoredFiles),
                                                   'tokens'          => 0,
                                                   'file_extensions' => json_encode($this->config->file_extensions),
                                                   'ignore_dirs'     => json_encode($this->config->ignore_dirs),
                                                   'include_dirs'    => json_encode($this->config->include_dirs),
                                               )
            );
            return;
        }

        $this->tmpFileName = "{$this->config->tmp_dir}/files{$this->config->pid}.txt";
        $tmpFiles = array_map(function (string $file): string {
            return str_replace(array('\\', '(', ')', ' ', '$', '<', "'", '"', ';', '&', '`', '|', "\t"),
                array('\\\\', '\\(', '\\)', '\\ ', '\\$', '\\<', "\\'", '\\"', '\\;', '\\&', '\\`', '\\|', "\\\t", ),
                ".$file");
        }, $files);
        file_put_contents($this->tmpFileName, implode("\n", $tmpFiles));

        $SQLresults = $this->checkCompilations();

        $SQLresults += $this->checkShortTags();

        $i = array();
        foreach ($ignoredFiles as $file => $reason) {
            $i[] = compact('file', 'reason');
        }
        $ignoredFiles = $i;
        $this->datastore->cleanTable('ignoredFiles');
        $this->datastore->addRow('ignoredFiles', $ignoredFiles);

        $this->datastore->cleanTable('files');

        $this->datastore->addRow('files', $filesRows);
        $this->datastore->addRow('hash', array('files'           => count($files),
                                               'filesIgnored'    => count($ignoredFiles),
                                               'tokens'          => 0,
                                               'file_extensions' => json_encode($this->config->file_extensions),
                                               'ignore_dirs'     => json_encode($this->config->ignore_dirs),
                                               'include_dirs'    => json_encode($this->config->include_dirs),
                                               )
        );
        $this->datastore->reload();

        $stats['php'] = count($files);
        $this->datastore->addRow('hash', $stats);

        // check for special files
        display('Check config files');
        // Avoid , GLOB_BRACE
        $files = array_merge(glob("{$this->config->code_dir}/.*") ?: array(),
            glob("{$this->config->code_dir}/*") ?: array()) ;
        $files = array_map('basename', $files);

        $services = json_decode(file_get_contents("{$this->config->dir_root}/data/serviceConfig.json"));

        $configFiles = array();
        foreach ($services as $name => $service) {
            $diff = array_intersect((array) $service->file, $files);
            foreach ($diff as $d) {
                $configFiles[] = array('file'     => $d,
                                       'name'     => $name,
                                       'homepage' => $service->homepage);
            }
        }
        $this->datastore->addRow('configFiles', $configFiles);
        // Composer is checked previously

        $files = array();
        $i = 0;
        while (count($files) != $SQLresults) {
            $files = glob("{$this->config->project_dir}/.exakat/dump-*.php");
            usleep(random_int(0,1000) * 1000);

            ++$i;
            if ($i >= 60) {
                break 1;
            }
        }
        // TODO : log it when

        foreach ($files as $file) {
            include $file;

            $this->datastore->storeQueries($queries);
            unlink($file);
        }

        display('Done');

        if ($this->config->json) {
            echo json_encode($stats);
        }
        $this->datastore->addRow('hash', array('status' => 'Initproject'));
        $this->checkTokenLimit();
    }

    private function checkComposer(string $dir): void {
        // composer.json
        display('Check composer');
        $composerInfo = array();
        if ($composerInfo['composer.json'] = file_exists("{$dir}/composer.json")) {
            $composerInfo['composer.lock'] = file_exists("{$dir}/composer.lock");

            $composer = json_decode(file_get_contents("{$dir}/composer.json"));

            if (isset($composer->autoload)) {
                $composerInfo['autoload'] = isset($composer->autoload->{'psr-0'}) ? 'psr-0' : 'psr-4';
            } else {
                $composerInfo['autoload'] = false;
            }

            if (isset($composer->require)) {
                $this->datastore->addRow('composer', (array) $composer->require);
            }
        }

        $this->datastore->addRow('hash', $composerInfo);
    }

    private function checkLicence(string $dir): bool {
        $licenses = parse_ini_file($this->config->dir_root . '/data/license.ini');
        $licenses = $licenses['files'];

        foreach ($licenses as $file) {
            if (file_exists("$dir/$file")) {
                $this->datastore->addRow('hash', array('licence_file' => 'unknown'));

                return true;
            }
        }
        $this->datastore->addRow('hash', array('licence_file' => 'unknown'));

        return false;
    }

    public function __destruct() {
        if (file_exists($this->tmpFileName)) {
            unlink($this->tmpFileName);
        }
        if (file_exists($this->config->tmp_dir . '/lint.php')) {
            unlink($this->config->tmp_dir . '/lint.php');
        }
        if (file_exists($this->config->tmp_dir . '/lint_short_tags.php')) {
            unlink($this->config->tmp_dir . '/lint_short_tags.php');
        }
    }

    private function checkCompilations(): int {
        $versions = Config::PHP_VERSIONS;
        $SQLresults = 0;

        $analyzingVersion = $this->config->phpversion[0] . $this->config->phpversion[2];
        $this->datastore->cleanTable("compilation$analyzingVersion");
        if ($this->is_subtask === self::IS_SUBTASK) {
            $id = array_search($analyzingVersion, $versions);
            unset($versions[$id]);
        }

        foreach ($versions as $version) {
            $phpVersion = "php$version";

            if (empty($this->config->{$phpVersion})) {
                // This version is not defined
                continue;
            }

            display("Check compilation for $version");

            try {
                $php = new Phpexec($phpVersion, $this->config->{$phpVersion});
            } catch (\Exception $e) {
                // Skip compilation check if PHP is not available
                continue;
            }
            $php->compileFiles($this->config->code_dir, $this->tmpFileName, $this->config->dir_root);
            ++$SQLresults;
        }

        return $SQLresults;
    }

    private function checkShortTags(): int {
        copy("{$this->config->dir_root}/server/lint_short_tags.php", "{$this->config->project_dir}/.exakat/lint_short_tags.php");
        $shell = "nohup php {$this->config->project_dir}/.exakat/lint_short_tags.php {$this->config->php} {$this->config->project_dir} {$this->tmpFileName} 2>&1 >/dev/null & echo $!";
        shell_exec($shell);

        return 1;
    }
}

?>