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

use Exakat\Configsource\CommandLine;

class Exakat {
	public const VERSION = '2.4.7';
	public const BUILD = 1382;
	
	private Config $config;

	public function __construct() {
		$this->config = exakat('config');
	}

	public function execute(): void {
		switch ($this->config->command) {
			case 'cobble' :
				$task = new Tasks\Cobble();
				$task->run();
				break;

			case 'testcobble' :
				$task = new Tasks\Testcobble();
				$task->run();
				break;

			case 'doctor' :
				$doctor = new Tasks\Doctor();
				$doctor->run();
				break;

			case 'init' :
				$task = new Tasks\Initproject();
				$task->run();
				break;

			case 'anonymize' :
				$task = new Tasks\Anonymize();
				$task->run();
				break;

			case 'files' :
				$task = new Tasks\Files();
				$task->run();
				break;

			case 'load' :
				$task = new Tasks\Load();
				$task->run();
				break;

			case 'stat' :
				$task = new Tasks\Stat();
				$task->run();
				break;

			case 'catalog' :
				$task = new Tasks\Catalog();
				$task->run();
				break;

			case 'analyze' :
				$task = new Tasks\Analyze();
				$task->run();
				break;

			case 'results' :
				$task = new Tasks\Results();
				$task->run();
				break;

			case 'export' :
				$task = new Tasks\Export();
				$task->run();
				break;

			case 'report' :
				$task = new Tasks\Report();
				$task->run();
				break;

			case 'project' :
				$task = new Tasks\Project();
				$task->run();
				break;

			case 'clean' :
				$task = new Tasks\Clean();
				$task->run();
				break;

			case 'status' :
				$task = new Tasks\Status();
				$task->run();
				break;

			case 'help' :
				$task = new Tasks\Help();
				$task->run();
				break;

			case 'cleandb' :
				$task = new Tasks\CleanDb();
				$task->run();
				break;

			case 'update' :
				$task = new Tasks\Update();
				$task->run();
				break;

			case 'dump' :
				$task = new Tasks\Dump();
				$task->run();
				break;

			case 'test' :
				$task = new Tasks\Test();
				$task->run();
				break;

			case 'remove' :
				$task = new Tasks\Remove();
				$task->run();
				break;

			case 'upgrade' :
				$task = new Tasks\Upgrade();
				$task->run();
				break;

			case 'baseline' :
				$task = new Tasks\Baseline();
				$task->run();
				break;

			case 'config' :
				$task = new Tasks\Config();
				$task->run();
				break;

			case 'show' :
				$task = new Tasks\Show();
				$task->run();
				break;

			case 'install' :
				$task = new Tasks\Install();
				$task->run();
				break;

			default :
				$command_value = $this->config->command_value;
				$suggestions = array_filter(array_keys(CommandLine::$commands), function (string $x) use ($command_value): bool { similar_text((string) $command_value, $x, $percentage); return $percentage > 60; });

				print (!empty($command_value) ? "Unknown command '{$this->config->command_value}'. See https://exakat.readthedocs.io/en/latest/Administrator/Commands.html" . PHP_EOL : '') .
					  (!empty($suggestions) ? 'Did you mean : ' . implode(', ', $suggestions) . ' ? ' : '') . PHP_EOL;

				// fallthrough

			case 'version' :
				$version = self::VERSION;
				$build = self::BUILD;
				$date = date('r', filemtime(__FILE__));
				echo "
 ________                 __              _    
|_   __  |               [  |  _         / |_  
  | |_ \_| _   __  ,--.   | | / ]  ,--. `| |-' 
  |  _| _ [ \ [  ]`'_\ :  | '' <  `'_\ : | |   
 _| |__/ | > '  < // | |, | |`\ \ // | |,| |,  
|________|[__]`\_]\'-;__/[__|  \_]\'-;__/\__/  
                                               

Exakat : @ 2014-2022 Damien Seguy - Exakat SAS <contact(at)exakat.io>. 
Version : ", $version, ' - Build ', $build, ' - ', $date, "\n";

				break;
		}
	}
}

?>
