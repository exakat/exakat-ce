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

namespace Exakat\Tasks\Helpers;


class ClassTraitContext {
    public const NO_CLASS_TRAIT_CONTEXT = null;

    private array          $contexts = array();
    private ?AtomInterface $last     = self::NO_CLASS_TRAIT_CONTEXT;

    public function __construct() {    }

    public function getCurrent(): ?AtomInterface {
        return $this->last;
    }

    public function pushContext(?AtomInterface $context): void {
        $this->contexts[] = $context;
        $this->last = $context;
    }

    public function popContext() {
        array_pop($this->contexts);
        $this->last = end($this->contexts) ?: self::NO_CLASS_TRAIT_CONTEXT;
    }

}

?>
