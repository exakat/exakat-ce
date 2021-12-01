<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy Ð Exakat SAS <contact(at)exakat.io>
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

use Exakat\Exceptions\VcsSupport;
use Exakat\Vcs\Vcs;
use Exakat\Export\Export as ExportFormat;

class Export extends Tasks {
    public const CONCURENCE = self::ANYTIME;

    public const OPEN_TAG  = '<?php ' . PHP_EOL;
    public const CLOSE_TAG = PHP_EOL . '?' . '>';

    public const NO_NEXT = -1;

    private $sequenceFor     = false;
    private $insideInterface = false;
    private $dictionary      = null;
    private $php             = null;

    public function run(): void {
        $gremlinVersion = $this->gremlin->serverInfo()[0];
        $this->dictionary = exakat('dictionary');
        $this->php = exakat('php');

        if (version_compare($gremlinVersion, '3.4.0') >= 0) {
            $queryTemplate = 'g.V().valueMap().with(WithOptions.tokens).by(unfold())';
        } else {
            $queryTemplate = 'g.V()';
        }

        $vertices = $this->gremlin->query($queryTemplate, array());

        $V = array();
        $root = 0;
        foreach($vertices as $v) {
            if ($v['label'] === 'Project') {
                $root = $v['id'];
            }
            $V[$v['id']] =  $v;
        }

        if (version_compare($gremlinVersion, '3.4.0') >= 0) {
            $queryTemplate = 'g.E().as("e").outV().as("outV").select("e").inV().as("inV").select("e", "inV", "outV").by(valueMap(true).by(unfold())).by(id()).by(id())';
        } else {
            $queryTemplate = 'g.E()';
        }
        $edges = $this->gremlin->query($queryTemplate);

        $E = array();
        foreach($edges as $e) {
            // Special for version 3.4
            if (isset($e['e'])) {
                $e = array_merge($e, $e['e']);
            }
            $id = $e['outV'];

            if (!isset($E[$id])) {
                $E[$id] = array();
            }

            array_collect_by($E[$id], $e['inV'], $e['label']);
        }

        if (in_array('Dot', $this->config->project_reports)) {
            $renderer = ExportFormat::getInstance('Dot', $V, $E);
            $text = $renderer->render($root);
            $extension = $renderer->getExtension();
        } elseif (in_array('Php', $this->config->project_reports)) {
            $renderer = ExportFormat::getInstance('Php', $V, $E);
            $text = $renderer->render($root);
            $files = json_decode($text, true);
            $extension = $renderer->getExtension();

            if ($this->config->json) {
                echo $text;

                return;
            }

            if (!empty($this->config->filename)) {
                // only one file, the first one
                file_put_contents($this->config->filename[0], array_pop($files));

                $this->checkCompilation($this->config->filename[0]);

                return;
            }

            if (!empty($this->config->dirname)) {
                foreach($files as $file => $code) {
                    if (!file_exists($this->config->dirname . '/' . dirname($file))) {
                        mkdir($this->config->dirname . '/' . dirname($file), 0755);
                    }
                    file_put_contents($this->config->dirname . '/' . $file, $code);
                    $this->checkCompilation($this->config->dirname . '/' . $file);
                }

                return;
            }

            if (!empty($this->config->inplace)) {
                display("Writing in place\n");
                foreach($files as $file => $code) {
                    file_put_contents($this->config->code_dir . '/' . $file, $code);
                    $this->checkCompilation($this->config->code_dir . '/' . $file);
                }

                return;
            }

            if (!empty($this->config->branch)) {
                try {
                    display("Writing in branch : {$this->config->branch}\n");

                    $vcs = Vcs::getVcs($this->config);
                    $vcs = new $vcs($this->config->project, $this->config->code_dir);
                    $branch = $vcs->getBranch();
                    $vcs->createBranch($this->config->branch);

                    foreach($files as $file => $code) {
                        file_put_contents($this->config->code_dir . '/' . $file, $code);
                        $this->checkCompilation($this->config->code_dir . '/' . $file);
                    }

                    $vcs->commitFiles('Exakat cobbler for ' . implode(', ', $this->config->program));
                    $vcs->checkoutBranch($branch);
                } catch (VcsSupport $e) {
                    print "Error while storing in branch {$this->config->branch} : " . $e->getMessage() . "\n";
                }

                return;
            }

            $text = implode('', $files);
        } elseif (in_array('Table', $this->config->project_reports)) {
            $renderer = ExportFormat::getInstance('Table', $V, $E);
            $text = $renderer->render($root);
            $extension = $renderer->getExtension();
        } else {
            $renderer = ExportFormat::getInstance('Text', $V, $E);
            $text = $renderer->render($root);
            $extension = $renderer->getExtension();
        }

        if ($filenames = $this->config->filename) {
            foreach($filenames as $filename) {
                $filename = array_pop($filenames);
                if (in_array('Dot', $this->config->project_reports)) {
                    $fp = fopen($filename . '.dot', 'w+');
                } elseif (in_array('Php', $this->config->project_reports)) {
                    $fp = fopen($filename . '.php', 'w+');
                } else {
                    // todo : case this is a folder
                    $fp = fopen($filename, 'w+');
                }
                fwrite($fp, $text);
                fclose($fp);
            }
        } else {
            echo $text;
        }
    }

    private function checkCompilation(string $file): bool {
        if (!($compile = $this->php->compile($file))) {
            display("Warning : '{$file}' doesn't compile. Proceed with caution.");
        }

        return $compile;
    }
}

?>
