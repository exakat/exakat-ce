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


class FunctionContext {
    private const NO_FUNCTION = 'Not in Function Context';
    private const NO_CODE     = 'Not in Function Context (code)';
    private const GLOBAL      = 'global';

    private array          $currentFunction = array();
    private ?AtomInterface  $lastAtom = null;

    public function add(AtomInterface $atom): void {
        $this->currentFunction[] = $atom;
        $this->lastAtom          = $atom;
    }

    public function current(): AtomInterface {
        return $this->lastAtom;
    }

    public function currentFullnspath(): string {
        return $this->lastAtom === null ? self::GLOBAL : $this->lastAtom->fullnspath;
    }

    public function currentCode(): string {
        return $this->lastAtom->code ?? self::GLOBAL;
    }

    public function remove(): void {
        array_pop($this->currentFunction);
        if (empty($this->currentFunction)) {
            $this->lastAtom = null;
        } else {
            $this->lastAtom = end($this->currentFunction);
        }
    }
}

?>
