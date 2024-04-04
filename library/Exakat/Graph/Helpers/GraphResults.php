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

namespace Exakat\Graph\Helpers;

use stdClass;

class GraphResults implements \ArrayAccess, \Iterator, \Countable {
    public const EMPTY   = 0;
    public const SCALAR  = 1;
    public const ARRAY   = 2;

    private int $type = self::EMPTY;
    private ?array $data  = null;

    public function __construct(?array $data = null) {
        // Case of empty result set.

        // A garder. Aucun résultat.
        if ($data === null) {
            $this->type = self::EMPTY;
            $this->data = $data;

            return;
        }

        if (isset($data[0])) {
            $this->type = self::ARRAY;
            $this->data = $data;
            $this->checkArray();
        } else {
            $this->type = self::EMPTY;
            $this->data = null;
        }
    }

    private function checkArray(): void {
        if (empty($this->data)) {
            return;
        }
        $data = array_values($this->data);
        if (!($data[0] instanceof stdClass)) {
            return;
        }

        foreach ($this->data as &$data) {
            $data = (array) $data;
        }
        unset($data);
    }

    public function deHash(?array $extra = null): void {
        if (empty($this->data)) {
            return;
        }

        $result = array();
        foreach ($this->data as $value) {
            foreach ($value as $k => $v) {
                $result[] = array('', $k, $v);
            }
        }
        if ($extra !== null) {
            $result = array_map(function (array $x) use ($extra): array {
                return array_merge($x, $extra);
            }, $result);
        }

        $this->data = $result;
    }

    public function string2Array(?array $extra = null): void {
        if (empty($this->data)) {
            return;
        }

        $result = array();
        foreach ($this->data as $value) {
            $result[] = array('', array_pop($value));
        }
        if ($extra !== null) {
            $result = array_map($result, function (array $x) use ($extra): array {
                return array_merge($x, $extra);
            });
        }

        $this->data = $result;
    }

    public function isEmpty(): bool {
        return $this->type === self::EMPTY;
    }

    public function toArray(): array {
        if ($this->type === self::EMPTY) {
            return array();
        } else {
            return $this->data;
        }
    }

    public function toHash(string $key, string $value): array {
        if ($this->type === self::EMPTY) {
            return array();
        }

        $return = array();
        foreach ($this->data as $row) {
            $return[$row[$key]] = $row[$value];
        }

        return $return;
    }

    public function toString(): string {
        return (string) ($this->data[0] ?? '');
    }

    public function toInt(): int {
        if ($this->data === null) {
            return 0;
        }

        return (int) $this->data[0];
    }

    public function toUuid(): string {
        if ($this->data === null) {
            return '';
        }

        return (string) $this->data[0];
    }

    public function isType(int $type): bool {
        return $this->type === $type;
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->data[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed {
        return $this->data[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        // Nothing. No update on that result
    }

    public function offsetUnset(mixed $offset): void {
        // Nothing. No update on that result
    }

    public function rewind(): void {
        if ($this->type === self::ARRAY) {
            reset($this->data);
        }
    }

    #[\ReturnTypeWillChange]
    public function current(): mixed {
        return current($this->data);
    }

    #[\ReturnTypeWillChange]
    public function key(): mixed {
        if ($this->type === self::ARRAY) {
            return key($this->data);
        }

        return '';
    }

    public function next(): void {
        next($this->data);
    }

    public function valid(): bool {
        if ($this->type === self::ARRAY) {
            return key($this->data) !== null;
        }

        return false;
    }

    public function count(): int {
        if ($this->type === self::ARRAY) {
            return count($this->data);
        }
        return 0;
    }
}

?>
