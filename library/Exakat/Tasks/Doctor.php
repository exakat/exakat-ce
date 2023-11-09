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
use Exakat\Config;
use Exakat\Phpexec;
use Exakat\Graph\Graph;
use Exakat\Exceptions\NoPhpBinary;
use Exakat\Exceptions\HelperException;
use Exakat\Exceptions\NoSuchReport;
use Exakat\Tasks\Helpers\ReportConfig;
use Symfony\Component\Yaml\Yaml as Symfony_Yaml;
use Exakat\Stubs\Stubs;

class Doctor extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    protected string $logname = self::LOG_NONE;

    private array $reportList = array();

    public function __construct() {
        $this->config  = exakat('config');
        $this->gremlin = exakat('graphdb');
        // Ignoring everything else
    }

    public function run(): void {
        $stats = array_merge($this->checkPreRequisite(),
            $this->checkAutoInstall());

        $phpBinaries = array('php' . str_replace('.', '', substr(PHP_VERSION, 0, 3)) => PHP_BINARY);
        foreach (Config::PHP_VERSIONS as $shortVersion) {
            $configName = "php$shortVersion";
            if (!empty($this->config->$configName)) {
                $phpBinaries[$configName] = $this->config->$configName;
            }
        }

        $stats = array_merge($stats,
            $this->checkPHPs($phpBinaries));

        if ($this->config->verbose === true) {
            $stats = array_merge($stats, $this->checkOptional());
        }

        if ($this->config->json === true) {
            print json_encode($stats, JSON_PRETTY_PRINT);
            return;
        }

        if ($this->config->yaml === true) {
            print Symfony_Yaml::dump($stats);
            return;
        }

        if ($this->config->quiet !== true) {
            print $this->displayCli($stats);
        }
    }

    private function displayCli(array $stats): string {
        $stats['exakat']['rulesets']       = $this->array2list($stats['exakat']['rulesets']);
        $stats['exakat']['extra rulesets'] = $this->array2list($stats['exakat']['extra rulesets']);
        $stats['exakat']['ignored rules']  = $this->array2list($stats['exakat']['ignored rules']);
        $stats['exakat']['exakat.ini']     = $this->array2list($stats['exakat']['exakat.ini']);
        $stats['exakat']['reports']        = $this->array2list($stats['exakat']['reports'] );

        if (isset($stats['exakat']['extensions'])) {
            $stats['exakat']['extensions']  = $this->array2list($stats['exakat']['extensions']);
        }

        $doctor = '';
        foreach ($stats as $section => $details) {
            $doctor .= $section . ' : ' . PHP_EOL;
            foreach ($details as $k => $v) {
                $doctor .= '    ' . substr("$k                          ", 0, 20) . ' : ' . $v . PHP_EOL;
            }
            $doctor .= PHP_EOL;
        }

        return $doctor;
    }

    private function checkPreRequisite(): array {
        $stats = array();

        // Compulsory
        $stats['exakat']['executable']  = $this->config->executable;
        $stats['exakat']['version']     = Exakat::VERSION;
        $stats['exakat']['build']       = Exakat::BUILD;
        $stats['exakat']['exakat.ini']  = $this->config->configFiles;
        $stats['exakat']['graphdb']     = $this->config->graphdb;
        $reportList = array();
        foreach ($this->config->project_reports as $project_report) {
            try {
                $reportConfig = new ReportConfig('Reports/' . $project_report, $this->config);
            } catch (NoSuchReport $e) {
                display($e->getMessage());
                continue;
            }
            $this->reportList[] = $reportConfig->getName();
        }
        sort($this->reportList);
        $stats['exakat']['reports']        = $reportList;

        $stats['exakat']['rulesets']       = $this->config->project_rulesets;
        $stats['exakat']['extra rulesets'] = array_keys($this->config->rulesets);
        $stats['exakat']['ignored rules']  = $this->config->ignore_rules;

        $stats['exakat']['tokenslimit'] = number_format((int) $this->config->token_limit, 0, '', ' ');
        if ($list = $this->config->ext->getPharList()) {
            $stats['exakat']['extensions']  = $list;
        }

        $stubs = new Stubs(dirname($this->config->ext_root) . '/stubs/',
            $this->config->stubs,
        );
        $files = $stubs->getFile();
        $stats['exakat']['stubs'] = makeList($files, '');

        // check for running PHP
        $stats['PHP']['binary']                 = phpversion();
        $stats['PHP']['memory_limit']           = ini_get('memory_limit');
        $stats['PHP']['short_open_tags']        = (ini_get('short_open_tags') ? 'On' : 'Off');
        $stats['PHP']['ext/curl']               = extension_loaded('curl') ? 'Yes' : 'No (Compulsory, please install it with --with-curl)';
        $stats['PHP']['ext/hash']               = extension_loaded('hash') ? 'Yes' : 'No (Compulsory, please install it with --enable-hash)';
        $stats['PHP']['ext/phar']               = extension_loaded('phar') ? 'Yes' : 'No (Needed to run exakat.phar. please install by default)';
        $stats['PHP']['ext/sqlite3']            = extension_loaded('sqlite3') ? 'Yes' : 'No (Compulsory, please install it by default (remove --without-sqlite3))';
        $stats['PHP']['ext/tokenizer']          = extension_loaded('tokenizer') ? 'Yes' : 'No (Compulsory, please install it by default (remove --disable-tokenizer))';
        $stats['PHP']['ext/mbstring']           = extension_loaded('mbstring') ? 'Yes' : 'No (Compulsory, add --enable-mbstring to configure)';
        $stats['PHP']['ext/json']               = extension_loaded('json') ? 'Yes' : 'No';
        $stats['PHP']['ext/xmlwriter']          = extension_loaded('xmlwriter') ? 'Yes' : 'No (Optional, used by XML reports)';
        $stats['PHP']['ext/openssl']            = extension_loaded('openssl') ? 'Yes' : 'No (Optional, used for https access to repositories)';

        if (extension_loaded('xdebug') === true) {
            $stats['PHP']['xdebug.max_nesting_level']            = (ini_get('xdebug.max_nesting_level') ) . ' (Must be -1 or more than 1000)';
        }
        $stats['PHP']['pcre.jit']               = (ini_get('pcre.jit') ? 'On' : 'Off') . ' (Must be off on PHP 7.3 and OSX)';

        // java
        $res = shell_exec('java -version 2>&1') ?? '';
        if (stripos($res, 'command not found') !== false) {
            $stats['java']['installed'] = 'No';
            $stats['java']['installation'] = 'No java found. Please, install Java Runtime (SRE) 1.7 or above from java.com web site.';
        } elseif (preg_match('/(java|openjdk) version "(.*)"/is', $res, $r)) {
            $lines = explode(PHP_EOL, $res);
            $line2 = $lines[1];
            $stats['java']['installed'] = 'Yes';
            $stats['java']['type'] = trim($line2);
            $stats['java']['version'] = $r[1];
        } else {
            $stats['java']['error'] = $res;
            $stats['java']['installation'] = 'No java found. Please, install Java Runtime (SRE) 1.7 or above from java.com web site.';
        }
        $stats['java']['$JAVA_HOME'] = getenv('JAVA_HOME') ? getenv('JAVA_HOME') : '<none>';
        $stats['java']['$JAVA_OPTIONS'] = getenv('JAVA_OPTIONS') ?? ' (set $JAVA_OPTIONS="-Xms32m -Xmx****m", with **** = RAM in Mb. The more the better.';

        foreach (Graph::DRIVERS as $name => $driver) {
            $stats[$name] = Graph::getConnexion($this->config, $driver)->getInfo();
        }

        $stats['loader']['mode'] = $this->config->loader_mode;
        if ($this->config->loader_mode !== 'Serial') {
	        $stats['loader']['parallel max'] = $this->config->loader_parallel_max ?? 'N/A';
        }


        if ($this->config->project !== null) {
            $stats['project']['name']             = $this->config->project_name;
            $stats['project']['url']              = $this->config->project_url;
            $stats['project']['phpversion']       = $this->config->phpversion;
            $stats['project']['reports']          = makeList($this->reportList);
            $stats['project']['rulesets']         = makeList($this->config->project_rulesets  ?? array(), '');
            $stats['project']['included dirs']    = makeList($this->config->include_dirs      ?? array(), '');
            $stats['project']['ignored dirs']     = makeList($this->config->ignore_dirs       ?? array(), '');
            $stats['project']['ignored rules']    = makeList($this->config->ignore_rules      ?? array(), '');
            $stats['project']['file extensions']  = makeList($this->config->file_extensions   ?? array(), '');
        }

        return $stats;
    }

    private function checkAutoInstall(): array {
        $stats = array();

        // config
        if (!file_exists("{$this->config->projects_root}/config")) {
            mkdir("{$this->config->projects_root}/config", 0755);
        }

        if (file_exists("{$this->config->projects_root}/config/exakat.ini")) {
            $graphdb = $this->config->graphdb;
            $folder = '';
        } else {
            $ini = file_get_contents("{$this->config->dir_root}/server/exakat.ini");
            $version = PHP_MAJOR_VERSION . PHP_MINOR_VERSION;

            if (file_exists("{$this->config->projects_root}/tinkergraph")) {
                // This is the default expected folder
                $folder = 'tinkergraph';
                // tinkergraph or gsneo4j
                if (file_exists("{$this->config->projects_root}/tinkergraph/ext/neo4j-gremlin/")) {
                    $graphdb = 'gsneo4jv3';
                } else {
                    $graphdb = 'tinkergraphv3';
                }
            } else {
                $folder = '';
                $graphdb = 'nogremlin';
            }

            $ini = str_replace(array('{VERSION}', '{VERSION_PATH}',   '{GRAPHDB}', ";$graphdb", '{GRAPHDB}_path', ),
                array( $version,    $this->config->php, $graphdb,    $graphdb,    $folder),
                $ini);

            file_put_contents("{$this->config->projects_root}/config/exakat.ini", $ini);
        }

        $this->gremlin->setConfigFile();

        // projects
        if (file_exists("{$this->config->projects_root}/projects/")) {
            $stats['folders']['projects folder'] = 'Yes';
        } else {
            mkdir("{$this->config->projects_root}/projects/", 0755);
            if (file_exists("{$this->config->projects_root}/projects/")) {
                $stats['folders']['projects folder'] = 'Yes';
            } else {
                $stats['folders']['projects folder'] = 'No';
            }
        }

        // stubs
        if (!file_exists($this->config->dir_root . '/stubs')) {
//            mkdir($this->config->dir_root . '/stubs', 0755);
        }

        // projects
        if (file_exists('./projects') &&
            !file_exists("{$this->config->projects_root}/projects/test")) {
            $i = 0;
            do {
                ++$i;
                $id = random_int(0, PHP_INT_MAX);
            } while (file_exists("{$this->config->projects_root}/projects/test$id") && $i < 100);

            shell_exec('php exakat init -p test' . $id);

            rename("{$this->config->projects_root}/projects/test$id", "{$this->config->projects_root}/projects/test");
        }

        return $stats;
    }

    private function checkPHPs(array $config): array {
        $stats = array();

        foreach (Config::PHP_VERSIONS as $shortVersion) {
            $configVersion = "php$shortVersion";
            $version = "$shortVersion[0].$shortVersion[1]";
            if (isset($config[$configVersion])) {
                $stats[$configVersion] = $this->checkPHP($config[$configVersion], $version);
            }
        }

        return $stats;
    }

    private function checkOptional(): array {
        $stats = array();

        $optionals = array('Git'       => 'git',
                           'Mercurial' => 'hg',
                           'Svn'       => 'svn',
                           'Cvs'       => 'cvs',
                           'Bazaar'    => 'bzr',
                           'Composer'  => 'composer',
                           'Zip'       => 'zip',
                           'Rar'       => 'rar',
                           'Tarbz'     => 'tbz',
                           'Targz'     => 'tgz',
                           'SevenZ'    => '7z',
                          );

        foreach ($optionals as $class => $section) {
            try {
                $fullClass = "\Exakat\Vcs\\$class";
                $vcs = new $fullClass($this->config->project, $this->config->code_dir);
                $stats[$section] = $vcs->getInstallationInfo();
            } catch (HelperException $e) {
                $stats[$section] = array('installed' => 'No');
            }
        }

        return $stats;
    }

    private function checkPHP(string $pathToBinary, string $displayedVersion): array {
        $stats = array();

        $stats['configured'] = 'Yes (' . $pathToBinary . ')';

        try {
            $php = new Phpexec($displayedVersion, $pathToBinary);
            $stats['actual version'] = $php->getActualVersion();
            if (substr($stats['actual version'], 0, 3) === $this->config->phpversion) {
                $stats['auditing'] = 'with this version';
            }
        } catch (NoPhpBinary $e) {
            $stats['installed'] = 'Invalid path : ' . $pathToBinary;
        }
        return $stats;
    }

    private function array2list(array $array): string {
        return implode(",\n                           ", $array);
    }
}
?>
