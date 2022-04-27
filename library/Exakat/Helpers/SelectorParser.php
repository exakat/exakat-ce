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



namespace Exakat\Helpers;

/*
Currently tested with Ab [p=a],Ac [p = a, q = 2 ], Dc, Dc[a=1], Ee[r = "asdfadf"]

Selectors : * A A,B A,*  a[p],b[p2=b]
attributes : A[p] A[p="c"] A[p="c",p2="d"]
conbinator : A B, A > B, A >> B, A > B, C 


Possibles : A.b A#b, A:first, A:last, A:rank, A:link

Extension to other atoms then Name
Could we use actual LINKS ? 
differentiate between > (immediate) and >> (inside)
Needs a separator to allow multiple > in one atom

handles ' and " when they are around the string (or, by default, none)

*/

class SelectorParser {
    private $line = '';
    private $parsed = null;

    public function __construct(string $line) {
        $this->line = $line;
        $this->parsed = new Node('Root');
    }

    public function parse(): void {
        // catches A, with parameters (or not)
        preg_match_all('/(\s*(>>|>|,)\s*)?([A-Z][a-z]+)\s*(\[\s*([^\\]]*)\s*\])?/', $this->line, $r, PREG_SET_ORDER);

        $previous = null;
        $above = null;
        foreach($r as $R) {
            $node = new Node($R[3], $R[2]);

            $pattern = array();
            if (strpos($R[2], '>>') !== false) {
                $previous->setProperty($R[3], $node);
                $above = $previous;
            } elseif (strpos($R[2], '>') !== false) {
                $previous->setProperty($R[3], $node);
                $above = $previous;
            } elseif (strpos($R[2], ',') !== false) {
                $above->setProperty($R[3], $node);
            } else {
                $this->parsed->setProperty($R[3], $node);
            }

            if (!empty($R[4])) {
                preg_match_all('/\s*(.+?)\s*=\s*([^,]+)\s*,?/', substr($R[4], 1, -1), $r2, PREG_SET_ORDER);
                foreach($r2 as $R2) {
                    $node->setProperty($R2[1], $R2[2]);
                }
            }

            $previous = $node;
            // removes the reference
            unset($pattern);
        }
    }

    public function getAst(): node {
        return $this->parsed;
    }

/*
main => [a, b]
properties => [p => value|null, ]
*/
}

class Node {
    private string $inside = '';
    private string $link = '';
    private string $atom = '';
    private array $properties = array();

    public function __construct(string $atom, string $inside = '') {
        $this->atom = $atom;
        $this->inside = $inside;
    }

    public function setProperty($name, $value) {
        if ($name === 'link') {
            $this->link = $value;
        } else {
            $this->properties[$name] = $value;
        }
    }

    public function getProperty($name, $value) {
        return $this->properties[$name];
    }

    public function getInside() {
        return $this->inside;
    }

    public function getAtom() {
        return $this->atom;
    }

    public function getLink(): string {
        return $this->link;
    }

    public function issetProperty($name): bool {
        return isset($this->properties[$name]);
    }

    public function getProperties(): array {
        return $this->properties;
    }

    public function toArray(): array {
        $r = array('inside' => $this->inside,
                  );

        foreach($this->properties as $name => $property) {
            if ($property instanceof Node) {
                $r[$name] = $property->toArray();
            } else {
                $r[$name] = $property;
            }
        }

        return $r;
    }
}

?>