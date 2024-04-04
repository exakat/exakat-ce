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

class Cvs extends Vcs {
    private $executable = 'cvs';

    protected function selfCheck(): void {
        $res = $this->shell("{$this->executable} --version 2>&1");
        if (!str_contains($res, 'CVS')  ) {
            throw new HelperException('Cvs');
        }
    }

    public function clone(string $source): void {
        $this->check();

        $source = escapeshellarg($source);
        $this->shell("cd {$this->destinationFull}; {$this->executable} checkout --quiet $source code");
    }

    public function update(): string {
        $this->check();

        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} update");
        if (preg_match('/Updated to revision (\d+)\./', $res, $r)) {
            return $r[1];
        }

        return 'CSV updated to last revision';
    }

    public function getBranch(): string {
        return 'No branch';
    }

    public function getRevision(): string {
        return 'No revision';
    }

    public function getInstallationInfo(): array {
        $stats = array();

        $res = trim($this->shell("{$this->executable} --version 2>&1"));
        if (preg_match('/Concurrent Versions System \(CVS\) ([0-9\.]+) /', $res, $r)) {//
            $stats['installed'] = 'Yes';
            $stats['version'] = $r[1];
        } else {
            $stats['installed'] = 'No';
            $stats['optional'] = 'Yes';
        }

        return $stats;
    }

    public function getStatus(): array {
        $status = array('vcs'       => 'cvs',
                        'revision'  => $this->getRevision(),
                        'updatable' => false
                       );

        return $status;
    }

    public function getDiffLines(string $r1, string $r2): array {
        display("No support for line diff in CVS.\n");
        return array();
    }
}

?>