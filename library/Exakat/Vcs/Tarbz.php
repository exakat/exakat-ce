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
use Exakat\Exceptions\VcsError;
use Exakat\Exceptions\VcsSupport;

class Tarbz extends Vcs {
    private $executableTar   = 'tar';
    private $executableBzip2 = 'bzip2';

    protected function selfCheck(): void {
        $res = $this->shell("{$this->executableTar} --version 2>&1");
        if (!preg_match('#\d+\.\d+(\.\d+)?#s', $res)) {
            throw new HelperException('Tar');
        }

        $res = $this->shell("{$this->executableBzip2} --help 2>&1");
        if (strpos($res, 'bzip2') === false) {
            throw new HelperException('bzip2');
        }

        if (ini_get('allow_url_fopen') != true) {
            throw new HelperException('allow_url_fopen');
        }
    }

    public function clone(string $source): void {
        $this->check();

        $binary = file_get_contents($source);
        if (empty($binary)) {
            throw new VcsError("Error while loading tar.bz archive : archive is empty. Aborting\n");
        }

        $archiveFile = tempnam(sys_get_temp_dir(), 'archiveTgz') . '.tar.bz2';
        file_put_contents($archiveFile, $binary);

        $res = $this->shell("{$this->executableTar} -tjf $archiveFile 2>&1 >/dev/null");
        if (!empty($res)) {
            list($l) = explode("\n", $res, 1);

            throw new VcsError("Error while extracting tar.bz archive : \"$l\". Aborting\n");
        }

        $this->shell("mkdir {$this->destinationFull}; {$this->executableTar} -jxf $archiveFile --directory $this->destinationFull");

        unlink($archiveFile);
    }

    public function getInstallationInfo() {
        $stats = array();

        $res = trim($this->shell("{$this->executableTar} --version 2>&1"));
        if (preg_match('/^(\w+) ([0-9\.]+) /', $res, $r)) {//
            $stats['tar'] = 'Yes';
            $stats['tar version'] = $r[0];
        } else {
            $stats['tar'] = 'No';
            $stats['tar optional'] = 'Yes';
        }

        $res = trim($this->shell("{$this->executableBzip2} --help 2>&1"));
        if (preg_match('/Version ([0-9\.]+),/', $res, $r)) {//
            $stats['bzip2'] = 'Yes';
            $stats['bzip2 version'] = $r[1];
        } else {
            $stats['bzip2'] = 'No';
            $stats['bzip2 optional'] = 'Yes';
        }

        return $stats;
    }

    public function getStatus(): array {
        $status = array('vcs'       => 'tar.bz2',
                        'updatable' => false
                       );

        return $status;
    }

    public function createBranch(string $branch): bool {
        throw new VcsSupport('Zip', ' cannot create a new branch');

        return false;
    }

    public function checkoutBranch(string $branch = ''): bool {
        throw new VcsSupport('Zip', ' cannot checkout a branch');

        return false;
    }

    public function commitFiles(string $string): bool {
        throw new VcsSupport('Zip', ' cannot commit files');

        return false;
    }
}

?>