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

namespace Exakat\Tasks\Helpers;

use Exakat\GraphElements;
use Exakat\Exceptions\LoadError;

class Links {
    private array $links   = array();
    private array $allowed = array();

    private const EVERGREEN = array('CALLED' => 1,
                                    'RETURNED' => 1,
                                    'THROWN' => 1,
                                    'YIELDED' => 1,
                                    'PHPDOC' => 1,
                                    'NEXT' => 1,
                                   );

    public function __construct() {
        $this->allowed = array_merge(GraphElements::$LINKS, GraphElements::$LINKS_EXAKAT);
    }

    public function addLink(AtomInterface $origin, AtomInterface $destination, string $label): void {
        if (!in_array($label, $this->allowed, STRICT_COMPARISON)) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            throw new LoadError('Undefined link ' . $label . ' for atom ' . $origin->atom);
        }

        if (!isset(self::EVERGREEN[$label]) &&
            !in_array($label, GraphElements::$ATOMS_LINKS[$origin->atom])) {
            die("Cannot add link $label from $origin->atom\n");
        }

        $this->links[] = new LinksHolder($label, $origin->id, $destination->id);
    }

    public function count(): int {
        return count($this->links);
    }

    public function toArray(): array {
        return $this->links;
    }
}

?>
