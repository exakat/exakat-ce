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

namespace Exakat\Vcs;

use Exakat\Exceptions\HelperException;

class Svn extends Vcs {
    private array $info = array();
    private string $executable = 'svn';

    public function __construct(string $destination, string $project_root) {
        parent::__construct($destination, $project_root);
    }

    protected function selfCheck(): void {
        $res = $this->shell("{$this->executable} --version 2>&1");
        if (!str_contains($res, 'svn')  ) {
            throw new HelperException('SVN');
        }
    }

    public function clone(string $source): void {
        $this->check();

        $source = escapeshellarg($source);
        $codePath = dirname($this->destinationFull);
        $this->shell("cd {$codePath}; {$this->executable} checkout --quiet $source code");
    }

    public function update(): string {
        $this->check();

        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} update");
        if (preg_match('/Updated to revision (\d+)\./', $res, $r)) {
            return $r[1];
        }

        if (preg_match('/At revision (\d+)/', $res, $r)) {
            return $r[1];
        }

        return 'Error : ' . $res;
    }

    private function getInfo() {
        $res = trim($this->shell("cd {$this->destinationFull}; {$this->executable} info"));

        if (empty($res)) {
            $this->info['svn'] = '';

            return;
        }
        foreach (explode("\n", $res) as $info) {
            list($name, $value) = explode(': ', trim($info));
            $this->info[$name] = $value;
        }
    }

    public function getBranch(): string {
        if (empty($this->info)) {
            $this->getInfo();
        }

        return $this->info['Relative URL'] ?? 'trunk';
    }

    public function getRevision(): string {
        if (empty($this->info)) {
            $this->getInfo();
        }

        return $this->info['Revision'] ?? 'No Revision';
    }

    public function getInstallationInfo(): array {
        $stats = array();

        $res = trim($this->shell("{$this->executable} --version 2>&1"));
        if (preg_match('/svn, version ([0-9\.]+) /', $res, $r)) {//
            $stats['installed'] = 'Yes';
            $stats['version'] = $r[1];
        } else {
            $stats['installed'] = 'No';
            $stats['optional'] = 'Yes';
        }

        return $stats;
    }

    public function getStatus(): array {
        $status = array('vcs'       => 'svn',
                        'revision'  => $this->getRevision(),
                        'updatable' => false
                       );

        return $status;
    }

    public function getDiffLines(string $r1, string $r2): array {
        display("No support for line diff in SVN.\n");
        return array();
    }

    public function getLastCommitDate(): int {
        $res = trim($this->shell("cd {$this->destinationFull}; {$this->executable} info 2>&1"));

        //Last Changed Date: 2020-07-22 09:17:27 +0200 (Wed, 22 Jul 2020)
        if (preg_match('/Last Changed Date: (\d{4}.+\d{4}) /m', $res, $r)) {
            return strtotime($r[1]);
        } else {
            return 0;
        }
    }
}

?>