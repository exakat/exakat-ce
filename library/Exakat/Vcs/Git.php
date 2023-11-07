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

class Git extends Vcs {
    private bool   $installed  = false;
    private string $gitVersion = 'unknown';
    private string $executable = 'git';

    protected function selfCheck(): void {
        $res = $this->shell("{$this->executable} --version 2>&1");
        if (!str_contains($res, 'git')  ) {
            throw new HelperException('git');
        }

        if (preg_match('/git version ([0-9\.]+)/', trim($res), $r)) {
            $this->installed = true;
            $this->version   = $r[1];

            if (file_exists($this->destinationFull)) {
                $res = $this->shell("cd {$this->destinationFull}/; {$this->executable} branch | grep \\* 2>&1");
                $this->branch = substr(trim($res), 2);
            }
        } else {
            $this->installed = false;
        }
    }

    public function clone(string $source): void {
        $this->check();
        $repositoryDetails = parse_url($source);

        if (isset($repositoryDetails['user'])) {
            $repositoryDetails['user'] = urlencode($repositoryDetails['user']);
        } else {
            $repositoryDetails['user'] = '';
        }
        if (isset($repositoryDetails['pass'])) {
            $repositoryDetails['pass'] = urlencode($repositoryDetails['pass']);
        } else {
            $repositoryDetails['pass'] = '';
        }

        unset($repositoryDetails['query']);
        unset($repositoryDetails['fragment']);
        $repositoryNormalizedURL = unparse_url($repositoryDetails);

        $codePath = dirname($this->destinationFull);
        $shell = "cd $codePath; GIT_TERMINAL_PROMPT=0 {$this->executable} clone -q ";

        if (!empty($this->branch)) {
            display("Check out with branch '$this->branch'");
            $shell .= " -b $this->branch ";
        } elseif (!empty($this->tag)) {
            display("Check out with tag '$this->tag'");
            $shell .= " -b $this->tag ";
        } else {
            display('Check out with default branch');
        }

        $shell .= $repositoryNormalizedURL . ' code 2>&1 ';
        $shellResult = $this->shell($shell);

        if (($offset = strpos($shellResult, 'fatal: ')) !== false) {
            $errorMessage = str_replace($repositoryNormalizedURL, $source, $shellResult);
            $errorMessage = trim(substr($shellResult, $offset + 7));

            throw new VcsError('Git', $errorMessage);
        }
    }

    public function update(): string {
        $this->check();

        if (!file_exists($this->destinationFull . '/.git')) {
            display("This doesn't seem to be a git repository. Aborting\n");

            return self::NO_UPDATE;
        }

        $res = $this->shell("cd {$this->destinationFull}/; {$this->executable} branch | grep \\* 2>&1");
        $branch = substr(trim($res), 2);

        if (str_contains($branch, ' detached at ')  ) {
            $resInitial = $this->shell("cd {$this->destinationFull}/; {$this->executable} checkout --quiet; {$this->executable} pull; {$this->executable} branch | grep '* '");
            $branch = '';
        } else {
            $resInitial = $this->shell("cd {$this->destinationFull}/; {$this->executable} show-ref --heads $branch");
        }

        $this->shell("cd {$this->destinationFull}/;GIT_TERMINAL_PROMPT=0  {$this->executable} checkout $branch --quiet; {$this->executable} pull --quiet");

        $resFinal = $this->shell("cd {$this->destinationFull}/; {$this->executable} show-ref --heads $branch");
        if (str_contains($resFinal, ' ')  ) {
            list($resFinal) = explode(' ', $resFinal, 1);
        }

        return $resFinal;
    }

    public function setBranch(string $branch = ''): void {
        $this->branch = $branch;
    }

    public function setTag(string $tag = ''): void {
        $this->tag = $tag;
    }

    public function getBranch(): string {
        if (!file_exists("{$this->destinationFull}/")) {
            return '';
        }
        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} branch | grep \* 2>&1");
        $this->branch = trim($res, " *\n");

        return $this->branch;
    }

    public function getRevision(): string {
        if (!file_exists($this->destinationFull)) {
            return '';
        }

        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} rev-parse HEAD 2>&1");
        return trim($res);
    }

    public function getInstallationInfo(): array {
        $this->check();
        $stats = array('installed' => $this->installed === true ? 'Yes' : 'No',
                      );

        if ($this->installed === true) {
            $stats['version'] = $this->version;
            if (version_compare($this->version, '2.3') < 0) {
                $stats['version 2.3'] = 'It is recommended to use git version 2.3 or more recent (' . $this->version . ' detected), for security reasons and the support of GIT_TERMINAL_PROMPT';
            }
        } else {
            $stats['optional'] = 'Yes';
        }

        return $stats;
    }

    public function getStatus(): array {
        $name = $this->shell("{$this->executable} config user.name");
        $email = $this->shell("{$this->executable} config user.email");

        $status = array('vcs'        => 'git',
                        'branch'     => $this->getBranch(),
                        'revision'   => $this->getRevision(),
                        'vcs_update' => $this->getLastCommitDate(),
                        'updatable'  => true,
                        'name'       => $name,
                        'email  '    => $email,
                       );

        return $status;
    }

    public function getDiffLines(string $r1, string $r2): array {
        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} diff -U0 -r $r1 -r $r2");

        $file    = '';
        $changes = array();

        $lines = explode(PHP_EOL, $res);
        foreach ($lines as $line) {
            if (preg_match('#diff --git a(/.*?) b(/.*)#', $line, $r)) {
                $file = $r[1];
                continue;
            }

            if (preg_match('#@@ \-(\d+)(,(\d+))? \+(\d+)(,(\d+))?( )@@#', $line, $r, PREG_UNMATCHED_AS_NULL)) {
                $c = ((int) $r[6] ?? 1) - ((int) $r[3] ?? 1);
                if ($c !== 0) {
                    $changes[] = array('file' => $file,
                                       'line' => $r[1],
                                       'diff' => $c,
                                       );
                }
            }
        }

        return $changes;
    }

    public function getFileModificationLoad(): array {
        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} log --name-only --pretty=format:");

        $files = array();
        $rows = explode(PHP_EOL, $res);
        foreach ($rows as $row) {
            if (empty($row)) {
                continue;
            }
            if (isset($files[$row])) {
                ++$files[$row];
            } else {
                $files[$row] = 1 ;
            }
        }

        return $files;
    }

    public function getDiffFile(string $next): array {
        // Added and removed ?
        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} diff --diff-filter=a --name-only $next -- . ");

        if (empty($res)) {
            return array();
        }

        $return = explode("\n", trim($res));
        $return = array_map(function (string $x): string {
            return "/$x";
        }, $return);

        return $return;
    }

    public function checkOut(string $next): array {
        //--diff-filter=[(A|C|D|M|R|T|U|X|B)…​[*]]
        // Some situations are not supported yet.
        // We keep Added, Modified. Deleted are ignored, as non-treatable.
        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} diff --diff-filter=d --name-only $next -- . ");

        // No name, may be, but we still need to update the code
        $this->shell("cd {$this->destinationFull}; {$this->executable} checkout $next");

        if (empty($res)) {
            return array();
        }

        $return = explode("\n", trim($res));
        $return = array_map(function (string $x): string {
            return "/$x";
        }, $return);

        return $return;
    }

    public function getLastCommitDate(): int {
        return (int) strtotime(trim($this->shell("cd {$this->destinationFull}; {$this->executable} log -1 --format=%cd")));
    }

    public function hasBranch(string $branch = ''): bool {
        $branch = escapeshellarg($branch);
        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} branch -a | grep $branch");

        // fatal: A branch named 'freezy-sk-fix-typo' already exists.
        return !empty($res);
    }

    public function createBranch(string $branch = ''): bool {
        $branch = escapeshellarg($branch);
        $res = $this->shell("cd {$this->destinationFull}; {$this->executable} checkout -q -b $branch");

        // fatal: A branch named 'freezy-sk-fix-typo' already exists.
        return !str_starts_with($res, 'fatal:')  ;
    }

    public function checkoutBranch(string $branch = ''): bool {
        if (empty($branch)) {
            // go back to the last branch
            $res = $this->shell("cd {$this->destinationFull}; {$this->executable} checkout -q " . $this->branch);
        } else {
            $branch = escapeshellarg($branch);
            $res = $this->shell("cd {$this->destinationFull}; {$this->executable} checkout -q $branch");
        }

        //error: pathspec 'inexistant branch' did not match any file(s) known to git
        return !str_starts_with($res, 'error:')  ;
    }

    public function commitFiles(string $message = 'Exakat Cobbler created those files'): bool {
        $message = escapeshellarg($message);

        $this->shell(<<<SHELL
cd {$this->destinationFull}; 
{$this->executable} stage -A ; 
git commit -m $message
SHELL
        );

        return true;
    }
}

?>