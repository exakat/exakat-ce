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

class Bazaar extends Vcs {
    private string $executable = 'bzr';

    protected function selfCheck(): void {
        $res = $this->shell("{$this->executable} --version 2>&1");
        if (!str_contains($res, 'Bazaar')  ) {
            throw new HelperException('Bazar');
        }
    }

    public function clone(string $source): void {
        $this->check();

        $source = escapeshellarg($source);
        $this->shell("cd {$this->destinationFull}; {$this->executable} branch $source code");
    }

    public function update(): string {
        $this->check();

        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} update 2>&1");
        if (preg_match('/revision (\d+)/', $res, $r)) {
            return $r[1];
        } else {
            return '';
        }
    }

    public function getBranch(): string {
        $this->check();

        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} version-info 2>&1 | grep branch-nick");
        return trim(substr($res, 13), " *\n");
    }

    public function getRevision(): string {
        $this->check();

        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} version-info 2>&1 | grep revno");
        return trim(substr($res, 7), " *\n");
    }

    public function getInstallationInfo(): array {
        $stats = array();

        $res = trim($this->shell("{$this->executable} --version 2>&1"));
        if (preg_match('/Bazaar \(bzr\) ([0-9\.]+) /', $res, $r)) {//
            $stats['installed'] = 'Yes';
            $stats['version'] = $r[1];
        } else {
            $stats['installed'] = 'No';
            $stats['optional'] = 'Yes';
        }

        return $stats;
    }

    public function getStatus(): array {
        $status = array('vcs'       => 'bzr',
                        'branch'    => $this->getBranch(),
                        'revision'  => $this->getRevision(),
                        'updatable' => true,
                       );

        return $status;
    }

    public function getDiffLines(string $r1, string $r2): array {
        display("No support yet for line diff in Bazaar.\n");
        return array();
    }
}

?>