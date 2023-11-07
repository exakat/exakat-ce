<?php
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

declare(strict_types = 1);

namespace Exakat\Query;

use Exakat\Query\DSL\Command;
use Exakat\Project;

class Multiple {
    public const STOP_QUERY = 'filter{ false; }';
    public const NO_QUERY   = 'filter{ true;  }';

    public const TO_GREMLIN = true;
    public const NO_GREMLIN = false;

    public const QUERY_RUNNING = true;
    public const QUERY_STOPPED = false;

    public const QUERY_EMPTY = 'Query Empty';

    private const SACK = '.withSack(["m":[], "processed":0, "total":0])';

    private int     $id        ;
    private string  $analyzer  ;
    private string  $php       ;
    private array   $queries   ;

    public function __construct(int $id, Project $project, string $analyzer, string $php, array $dependsOn = array()) {
        $this->id        = $id;
        $this->analyzer  = $analyzer;
        $this->php       = $php;

        $this->queries['query'] = new Query($id, $project, $analyzer, $php, $dependsOn);
    }

    public function __call(string $name, array $args): self {
        foreach ($this->queries as $query) {
            $query->$name(...$args);
        }

        return $this;
    }

    public function side(): self {
        foreach ($this->queries as $name => $query) {
            if ($name === 'query') {
                continue;
            }
            $query->side();
        }

        $this->queries['query']->side();

        return $this;
    }

    public function prepareSide(): Command {
        foreach ($this->queries as $name => $query) {
            if ($name === 'query') {
                continue;
            }
            $query->prepareSide();
        }

        return $this->queries['query']->prepareSide();
    }

    public function prepareQuery(): bool {
        foreach ($this->queries as $name => $query) {
            if ($name === 'query') {
                continue;
            }
            $query->prepareQuery();
        }

        return $this->queries['query']->prepareQuery();
    }

    public function prepareRawQuery(): void {
        $this->queries['query']->prepareRawQuery();
    }

    public function printRawQuery(): void {
        $this->queries['query']->printRawQuery();
    }

    public function getQuery(): string {
        return $this->queries['query']->getQuery();
    }

    public function getArguments(): array {
        return $this->queries['query']->getArguments();
    }

    public function printQuery(): void {
        $this->queries['query']->printQuery();
    }

    private function prepareSack(array $commands): string {
        return $this->queries['query']->prepareSack($commands);
    }

    private function sackToGremlin(array $sack): string {
        return $this->queries['query']->sackToGremlin($sack);
    }

    public function canSkip(): bool {
        return $this->queries['query']->canSkip();
    }
}
?>