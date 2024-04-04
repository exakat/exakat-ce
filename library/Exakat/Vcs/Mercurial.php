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

namespace Exakat\Vcs;

use Exakat\Exceptions\HelperException;

class Mercurial extends Vcs {
    private string $executable = 'hg';

    protected function selfCheck(): void {
        $res = $this->shell("{$this->executable} --version 2>&1");
        if (!str_contains($res, 'Mercurial')  ) {
            throw new HelperException('Mercurial');
        }
    }

    public function clone(string $source): void {
        $this->check();

        $sourceArg = escapeshellarg($source);
        $codePath = dirname($this->destinationFull);
        $this->shell("cd {$codePath}; {$this->executable} clone $sourceArg code");
    }

    public function update(): string {
        $this->check();

        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} pull 2>&1; {$this->executable} update; {$this->executable} log -l 1");
        preg_match('/changeset:\s+(\S+)/', $res, $changeset);
        preg_match("/date:\s+([^\n]+)/", $res, $date);

        return "$changeset[1] ($date[1])";
    }

    public function getInstallationInfo(): array {
        $stats = array();

        $res = trim($this->shell($this->executable . ' --version 2>&1'));
        if (preg_match('/Mercurial Distributed SCM \(version ([0-9\.]+)\)/', $res, $r)) {//
            $stats['installed'] = 'Yes';
            $stats['version'] = $r[1];
        } else {
            $stats['installed'] = 'No';
            $stats['optional'] = 'Yes';
        }

        return $stats;
    }

    public function getBranch(): string {
        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} summary 2>&1 | grep branch");
        return trim(substr($res, 8), " *\n");
    }

    public function getRevision(): string {
        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} summary 2>&1 | grep parent");
        return trim(substr($res, 8), " *\n");
    }

    public function getStatus(): array {
        $status = array('vcs'       => 'hg',
                        'branch'    => $this->getBranch(),
                        'revision'  => $this->getRevision(),
                        'updatable' => true
                       );

        return $status;
    }

    public function getDiffLines(string $r1, string $r2): array {
        display("No support for line diff in Hg.\n");
        return array();
    }

    public function getLastCommitDate(): int {
        $res = trim($this->shell("cd {$this->destinationFull}; {$this->executable} log -l 1 2>&1"));

        //date:        Wed Jun 23 11:19:15 2010 -0700
        if (preg_match('/date:\s+(\S.+\d{4})/m', $res, $r)) {
            return strtotime($r[1]);
        } else {
            return 0;
        }
    }
}

?>