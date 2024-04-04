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

use Exakat\Config;
use Exakat\Exakat;

class Upgrade extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    public function run(): void {
        // Avoid downloading when it is not a phar
        if ($this->config->is_phar === Config::IS_NOT_PHAR) {
            print 'This command can only update a .phar version of exakat. Aborting.' . PHP_EOL;
            return;
        }

        $options = array(
            'http'=>array(
                'method' => 'GET',
                'header' => array('User-Agent: exakat-' . Exakat::VERSION)
            )
        );

        if (!empty($this->config->licence)) {
            print "Upgrading Exakat Enterprise version.\n";
            $options['http']['header'][] = 'Exakat-licence: ' . $this->config->licence;
        }

        $context = stream_context_create($options);
        $html = file_get_contents('https://www.exakat.io/versions/index.php', true, $context);

        if (empty($html)) {
            print 'Unable to reach server to fetch the versions. Try again later.' . PHP_EOL;
            return;
        }

        if (empty($this->config->version)) {
            if (preg_match('/Download exakat version (\d+\.\d+\.\d+) \(Latest\)/s', $html, $r) == 0) {
                print 'Unable to find the requested version. Make sure the version number is valid. ' . PHP_EOL;
                return;
            }

            $version = $r[1];
        } else {
            $version = $this->config->version;
            if (preg_match('/^\d+\.\d+\.\d+$/s', $version, $r) == 0) {
                print 'Version number could not be recognized. Remove the option -version, or provide a valid version number, like "1.8.7".' . PHP_EOL;
                return;
            }

            if (preg_match('/>exakat-' . $version . '.phar<\/a>/s', $html) !== 1) {
                preg_match_all('/>exakat-(\d+.\d+.\d+).phar<\/a>/s', $html, $r);
                foreach ($r[1] as $v) {
                    // quick distance calculation : convert 1.2.3 to 123 and make a substraction with the current version
                    $closeVersion[$v] = abs((int) str_replace('.', '', $v) - (int) str_replace('.', '', $version));
                }
                asort($closeVersion);

                $display = 'Unable to find version ' . $version;

                if (empty($closeVersion)) {
                    $display .= '.';
                } else {
                    $display .= PHP_EOL . '   May be you can use ' . implode(', ', array_slice(array_keys($closeVersion), 0, 5)) . '.';
                }

                $display .= PHP_EOL . 'Check https://www.exakat.io/versions/index.php for the current list of available versions.';

                print $display . PHP_EOL;
                return;
            }
        }

        if (version_compare(Exakat::VERSION, $version) !== 0) {
            echo 'This version may be updated from the current version ' , Exakat::VERSION , ' to ' , $version  , PHP_EOL;

            if ($this->config->update !== true) {
                print '  This is a dry-run: nothing is changed. You may run this command with -u option to upgrade to the latest exakat version.' . PHP_EOL;
                return;
            }

            echo '  Updating to version ' , $version , PHP_EOL;
            preg_match('#<pre id="sha256"><a href="index.php\?file=exakat-' . $version . '.phar.sha256">(.*?)</pre>#', $html, $r);
            $sha256 = strip_tags($r[1]);

            // Read what we can
            $phar = (string) file_get_contents('https://www.exakat.io/versions/index.php?file=exakat-' . $version . '.phar', true, $context);

            if (hash('sha256', $phar) !== $sha256) {
                print 'Error while checking exakat.phar\'s checksum. Aborting update. Please, try again' . PHP_EOL;
                return;
            }

            $path = sys_get_temp_dir() . '/exakat.1.phar';
            file_put_contents($path, $phar);
            print 'Setting up exakat.phar' . PHP_EOL;
            rename($path, 'exakat.phar');

            return;
        }

        if (version_compare(Exakat::VERSION, $r[1]) === 0) {
            print 'This is the latest version (' . Exakat::VERSION . ')' . PHP_EOL;
            return;
        }

        print 'This version is ahead of the latest publication (Current : ' . Exakat::VERSION . ', Latest: ' . $r[1] . ')' . PHP_EOL;
    }
}

?>
