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

namespace Exakat\Reports;


class Perfile extends Reports {
    public const FILE_EXTENSION = 'txt';
    public const FILE_FILENAME  = self::STDOUT;

    public function _generate(array $analyzerList): string {
        $analysisResults = $this->dump->fetchAnalysers($analyzerList);

        $perfile       = array();
        $titleCache    = array();
        $maxLine       = 0;
        $maxTitle      = 0;
        foreach ($analysisResults->toArray() as $row) {
            if ($row['line'] === -1) {
                continue;
            }
            $this->count();
            if (!isset($titleCache[$row['analyzer']])) {
                $titleCache[$row['analyzer']] = $this->docs->getDocs($row['analyzer'], 'name');
            }

            $maxLine = max($maxLine, $row['line']);
            $maxTitle = max($maxTitle, strlen($titleCache[$row['analyzer']]), strlen($row['file']));
            if (strlen($titleCache[$row['analyzer']]) > 40) {
                $title = substr($titleCache[$row['analyzer']], 0, 37) . '...';
            } else {
                $title = $titleCache[$row['analyzer']];
            }
            $perfile[$row['file']][] = sprintf(' % 4s %-40s %-40s', $row['line'], $title, $row['fullcode']);
            $this->count();
        }

        $text = '';
        $line = strlen((string) $maxLine) + $maxTitle + 10;

        foreach ($perfile as &$file) {
            sort($file);
        }
        unset($file);

        // sort by path
        ksort($perfile);

        foreach ($perfile as $file => $issues) {
            $text .= str_repeat('-', $line) . "\n" .
                     " line  $file\n" .
                     str_repeat('-', $line) . "\n" .
                     implode("\n", $issues) . "\n" .
                     str_repeat('-', $line) . "\n"

                     . "\n"
                     . "\n";
        }

        return $text;
    }
}

?>