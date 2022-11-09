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
    public const CLASS_CONTEXT = 'class';
    public const ALL_CONTEXT   = 'all';

    private array          $contexts = array();
    private ?AtomInterface $last       = self::NO_CLASS_TRAIT_CONTEXT;
    private ?AtomInterface $lastClass  = self::NO_CLASS_TRAIT_CONTEXT;

    public function getCurrent(string $type = self::ALL_CONTEXT): ?AtomInterface {
        if ($type === self::ALL_CONTEXT) {
            return $this->last;
        }

        return $this->lastClass;
    }

    public function pushContext(?AtomInterface $context): void {
        $this->contexts[] = $context;
        $this->last = $context;

        if ('Class' === ($context->atom ?? '')) {
            $this->lastClass = $context;
        }

        if ('Trait' === ($context->atom ?? '')) {
            $this->lastClass = $context;
        }
    }

    public function popContext() {
        $last = array_pop($this->contexts);
        $this->last = end($this->contexts) ?: self::NO_CLASS_TRAIT_CONTEXT;

        if ('Class' === ($last->atom ?? '')) {
            $this->lastClass = $this->last;
        }
    }
}

?>
