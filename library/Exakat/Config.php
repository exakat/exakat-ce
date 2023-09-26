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

use Exakat\Configsource\{CommandLine, DefaultConfig, DotExakatConfig, DotExakatYamlConfig, EmptyConfig, EnvConfig, ExakatConfig, ProjectConfig, RulesetConfig, Config as Configsource };
use Exakat\Exceptions\InaptPHPBinary;
use Exakat\Autoload\AutoloadDev;
use Exakat\Autoload\AutoloadExt;
use Phar;
use Exakat\Helpers\Directive;

class Config extends Configsource {
    public const PHP_VERSIONS = Phpexec::VERSIONS_COMPACT;

    public const INSIDE_CODE   = true;
    public const WITH_PROJECTS = false;

    public const IS_PHAR      = true;
    public const IS_NOT_PHAR  = false;

    public string $dir_root              = '.';
    public string $ext_root              = '.';
    public string $projects_root         = '.';
    public bool   $is_phar               = true;
    public string $executable            = '';
    public AutoloadExt $ext;
    public AutoloadDev $dev;

    private ?Configsource $projectConfig         = null;
    private ?Configsource $commandLineConfig     = null;
    private ?Configsource $defaultConfig         = null;
    private ?Configsource $exakatConfig          = null;
    private ?Configsource $dotExakatConfig       = null;  // For .yaml and .ini (in that order)
    private ?Configsource $envConfig             = null;
    private array $argv                  		 = array();
    private int $screen_cols        		     = 100;

    private array $configFiles = array();
    private array $rulesets    = array();

    public function __construct(array $argv) {
        $this->argv = $argv;

        $this->is_phar  = class_exists('\\Phar') && !empty(phar::running()) ? self::IS_PHAR : self::IS_NOT_PHAR;
        if ($this->is_phar === self::IS_PHAR) {
            $this->executable    = $_SERVER['SCRIPT_NAME'];
            $this->projects_root = substr(dirname(phar::running()), 7);
            $this->dir_root      = phar::running();
            $this->ext_root      = substr(dirname(phar::running()) . '/ext', 5);

            if (!file_exists("{$this->projects_root}/projects")) {
                mkdir("{$this->projects_root}/projects", 0755);
            }
            ini_set('error_log', "{$this->projects_root}/projects/php_error.log");
        } else {
            $this->executable    = $_SERVER['SCRIPT_NAME'];
            $this->dir_root      = dirname(__DIR__, 2);
            // Run projects in the working directory
            if (dirname($_SERVER['SCRIPT_FILENAME']) === 'bin'      &&
                dirname($_SERVER['SCRIPT_FILENAME'], 2) === 'vendor') {
                $this->projects_root = getcwd();
            } else {
                $this->projects_root = dirname(__DIR__, 2);
            }
            $this->ext_root      = "{$this->dir_root}/ext";

            assert_options(ASSERT_ACTIVE, 1);
            assert_options(ASSERT_BAIL, 1);

            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }

        $this->options['project'] = new Project();

        $this->loadConfig($this->options['project']);
    }

    public function loadConfig(Project $project): ?string {
        unset($this->argv[0]);

        // CLI has the project name. If not, then .exakat.yaml|ini, then .env then exakat, then fail.

        // Default values, the hard coded ones
        $this->defaultConfig = new DefaultConfig();

        // Default values, the one in the server config.
        $this->exakatConfig  = new ExakatConfig($this->projects_root);
        if ($file = $this->exakatConfig->loadConfig($project)) {
            $this->configFiles[] = $file;
        }

        // then read the config from the commandline (if any)
        $this->commandLineConfig = new CommandLine();
        $this->commandLineConfig->setArgs($this->argv);
        $this->commandLineConfig->loadConfig($project);

        // Environment values, the one in the server config.
        $this->envConfig = new EnvConfig();
        if ($file = $this->envConfig->loadConfig($project)) {
            $this->configFiles[] = $file;
        }

        // Project name comes from the CLI
        if ($this->commandLineConfig->get('project')->isDefault()) {
            // Trying to get the project name from the .yaml/.ini, in the local directory
            $this->dotExakatConfig = new DotExakatYamlConfig($this->options['project'], $this->projects_root);
            if (($file = $this->dotExakatConfig->loadConfig($this->options['project'])) === self::NOT_LOADED) {
                $this->dotExakatConfig = new DotExakatConfig($this->options['project'], $this->projects_root);
                $file = $this->dotExakatConfig->loadConfig($this->options['project']);
                if ($file === self::NOT_LOADED) {
                    $this->dotExakatConfig = new EmptyConfig();
                } else {
                    $this->configFiles[] = $file;
                }
            } else {
                $this->configFiles[] = $file;
            }

            $this->options['project'] = $this->dotExakatConfig->get('project');

            // project config is useless here, so it gets a default value
            $this->projectConfig = new EmptyConfig();
        } else {
            $this->options['project'] = $this->commandLineConfig->get('project');

            $this->projectConfig = new ProjectConfig($this->projects_root);
            if ($file = $this->projectConfig->loadConfig($this->options['project'])) {
                $this->configFiles[] = $file;
            }

            // @todo : also support json?
            $this->dotExakatConfig = new DotExakatYamlConfig($this->options['project'], $this->projects_root);
            if (($file = $this->dotExakatConfig->loadConfig($this->options['project'])) === self::NOT_LOADED) {
                $this->dotExakatConfig = new DotExakatConfig($this->options['project'], $this->projects_root);
                $file = $this->dotExakatConfig->loadConfig($this->options['project']);
                if ($file === self::NOT_LOADED) {
                    $this->dotExakatConfig = new EmptyConfig();
                } else {
                    $this->configFiles[] = $file;
                }
            } else {
                $this->configFiles[] = $file;
            }
        }

        // build the actual config. Project overwrite commandline overwrites config, if any.
        $this->options = array_merge($this->defaultConfig      ->toArray(),
                                     $this->exakatConfig       ->toArray(),
                                     $this->projectConfig      ->toArray(),
                                     $this->dotExakatConfig    ->toArray(), // yaml then ini
                                     $this->envConfig          ->toArray(),
                                     $this->commandLineConfig  ->toArray()
                                     );

        // @todo : consider 'onefile' as a separate config, as the others.
        if ($this->commandLineConfig->get('command') === 'onefile') {
            $this->options['project'] = $project;
        }

        if ( $this->commandLineConfig->get('project')->isDefault() && 
            !$this->dotExakatConfig->get('project')->isDefault()) {
            $this->options['project'] = $this->dotExakatConfig->get('project');
        }

        unset($this->options['project_themes']);
        $this->options['configFiles'] = $this->configFiles;

        if ($this->options['debug'] === true) {
            display("Debug mode\n");
            assert_options(ASSERT_ACTIVE, 1);
            assert_options(ASSERT_BAIL, 1);

            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }

        //program has precedence over rulesets
        if (isset($this->commandLineConfig->toArray()['program']) && 
        	!in_array($this->command, array('export', 'report'), \STRICT_COMPARISON)) {
            $this->options['project_rulesets'] = array();
            $this->options['project_reports'] = array();
        }

        $rulesets = new RulesetConfig($this->dir_root);
        if ($file = $rulesets->loadConfig($this->commandLineConfig->get('project'))) {
            $this->configFiles[] = $file;
            $this->rulesets = $rulesets->toArray();
        }

        // Local configuration adds to the server configuration
        if ($this->dotExakatConfig instanceof DotExakatYamlConfig) {
            $this->rulesets = array_merge($this->rulesets, $this->dotExakatConfig->getRulesets());
        }
        if ($this->dotExakatConfig instanceof DotExakatConfig) {
            $this->rulesets = array_merge($this->rulesets, $this->dotExakatConfig->getRulesets());
        }

        if ($this->options['command'] !== 'doctor') {
            $this->checkSelf();
        }

		$stubsDirectives = $this->commandLineConfig->toArray()['directives']['stubs'] ?? array();
		if ($stubsDirectives instanceof Directive) {
			$stubsDirectives = $stubsDirectives->list();
		} 
        $this->options['stubs'] = array_unique(array_merge($this->projectConfig->toArray()['stubs']                              ?? array(),
                                                           $this->exakatConfig->toArray()['stubs']                               ?? array(),
                                                           $this->dotExakatConfig->toArray()['stubs']                            ?? array(),
                                                           $stubsDirectives,
                                            ));

        // autoload dev
        $this->dev = new AutoloadDev($this->extension_dev ?? '');
        $this->dev->registerAutoload();

        // autoload extensions
        $this->ext = new AutoloadExt($this->ext_root);
        $this->ext->registerAutoload();

        $exts = glob($this->dir_root . '/library/Exakat/Analyzer/Extensions/Ext*');
        $exts = array_map(function (string $path): string { return strtolower(substr(basename($path, '.php'), 3));}, $exts);

        if (in_array('all', $this->options['php_extensions'])) {
            $this->options['php_extensions'] = $exts;
        } elseif (in_array('none', $this->options['php_extensions'])) {
            $this->options['php_extensions'] = array();
        } else {
            $this->options['php_extensions'] = array_filter($this->options['php_extensions'], function (string $name) use ($exts): bool { return in_array($name, $exts);});
        }

        $this->finishConfigs();

        // All other values are actually unset here, except projectConfig.
        $this->commandLineConfig     = null;
        $this->defaultConfig         = null;
        $this->dotExakatConfig       = null;
        $this->envConfig             = null;

        return 'main_config';
    }

    public function __toString(): string {
        $argv = implode(', ', $this->argv);

        $display = <<<TEXT
directive     : 
--------------------------------------------------------------------
project_dir   : {$this->options['project_dir']}  
code_dir      : {$this->options['code_dir']}     
log_dir       : {$this->options['log_dir']}      
tmp_dir       : {$this->options['tmp_dir']}      
datastore     : {$this->options['datastore']}    
dump          : {$this->options['dump']}         
dump_tmp      : {$this->options['dump_tmp']}     
argv          : $argv
--------------------------------------------------------------------

TEXT;

        return $display;
    }

    private function finishConfigs(): void {
        $this->options['pid'] = getmypid();

        if ($this->options['inside_code'] === self::INSIDE_CODE) {
            $this->options['project_dir']   = getcwd();
            $this->options['code_dir']      = getcwd();
            $this->options['log_dir']       = getcwd() . '/.exakat';
            $this->options['tmp_dir']       = getcwd() . '/.exakat';
            $this->options['datastore']     = getcwd() . '/.exakat/datastore.sqlite';
            $this->options['dump']          = getcwd() . '/.exakat/dump.sqlite';
            $this->options['dump_tmp']      = getcwd() . '/.exakat/.dump.sqlite';
            $this->options['dump_previous'] = 'none';
        } else {
            $this->options['project_dir']   = $this->projects_root . '/projects/' . ($this->options['project'] ?? '');
            $this->options['code_dir']      = $this->options['project_dir'] . '/code';
            $this->options['log_dir']       = $this->options['project_dir'] . '/log';
            $this->options['tmp_dir']       = $this->options['project_dir'] . '/.exakat';
            $this->options['datastore']     = $this->options['project_dir'] . '/datastore.sqlite';
            $this->options['dump']          = $this->options['project_dir'] . '/dump.sqlite';
            $this->options['dump_tmp']      = $this->options['project_dir'] . '/.dump.sqlite';
        }
    }

    public function __isset(string $name): bool {
        return isset($this->options[$name]);
    }

    public function __get(string $name) : mixed {
        if ($name === 'configFiles') {
            $return = $this->configFiles;
        } elseif ($name === 'rulesets') {
            $return = $this->rulesets;
        } elseif ($name === 'themas') {
            // @todo throw warning
            $return = $this->rulesets;
        } elseif (isset($this->options[$name])) {
            $return = $this->options[$name];
        } elseif ($name === 'screen_cols') {
            $return = $this->screen_cols;
        } elseif ($name === 'projectConfig') {
            $return = $this->projectConfig;
        } elseif ($name === 'exakatConfig') {
            $return = $this->exakatConfig;
        } else {
            $return = null;
        }

        return $return;
    }

    public function __set(string $name, mixed $value) : void {
        display("It is not possible to modify configuration $name with value '" . var_export($value, true) . "'\n");
    }

    private function checkSelf(): void {
        if (version_compare(PHP_VERSION, '7.4.0') < 0) {
            throw new InaptPHPBinary('PHP needs to be version 7.4.0 or more to run exakat.(' . PHP_VERSION . ' provided)');
        }
        $extensions = array('curl', 'mbstring', 'sqlite3', 'hash', 'json');

        $missing = array();
        foreach($extensions as $extension) {
            if (!extension_loaded($extension)) {
                $missing[] = $extension;
            }
        }

        if (!empty($missing)) {
           throw new InaptPHPBinary('PHP needs ' . (count($missing) == 1 ? 'one' : count($missing)) . ' extension' . (count($missing) > 1 ? 's' : '') . ' with the current version : ' . implode(', ', $missing));
        }
    }

    public function commandLineJson(): string {
        $return = $this->argv;

        $id = array_search('-remote', $return);
        unset($return[$id]);
        unset($return[$id + 1]);
        unset($return[0]);
        return json_encode(array_values($return));
    }

    public function duplicate(array $options): self {
        $return = clone $this;

        // Only update existing values : ignoring the rest
        foreach($options as $key => $value) {
            if (isset($return->options[$key])) {
                $return->options[$key] = $value;
                continue;
            }

            if (isset($this->$key)) {
                $return->rulesets = makeArray($value);
            }
        }

        return $return;
    }
}

?>