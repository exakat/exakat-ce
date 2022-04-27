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

use stdClass;

class Atom extends AtomInterface {
    public const STRING_MAX_SIZE = 500;

    public function __construct(int $id, string $atom, int $line, string $ws = '') {
        $this->id   = $id;
        $this->atom = $atom;
        $this->line = $line;
        $this->ws   = new Whitespace($ws);
    }

    public function __set($name, $value) {
        die("Fatal error : trying to set '$name' property on " . self::class);
    }

    public function __get($name) {
        die("Fatal error : trying to get '$name' property on " . self::class);
    }

    public function toArray(): array {
        if (strlen($this->code) > self::STRING_MAX_SIZE) {
            $this->code = substr($this->code, 0, self::STRING_MAX_SIZE) . '...[ total ' . strlen($this->code) . ' chars]';
        }
        if (strlen($this->lccode) > self::STRING_MAX_SIZE) {
            $this->lccode = substr($this->lccode, 0, self::STRING_MAX_SIZE) . '...[ total ' . strlen($this->lccode) . ' chars]';
        }
        if (strlen($this->fullcode) > self::STRING_MAX_SIZE) {
            $this->fullcode = substr($this->fullcode, 0, self::STRING_MAX_SIZE) . '...[ total ' . strlen($this->fullcode) . ' chars]';
        }

        $this->code          = $this->protectString($this->code       );
        $this->lccode        = $this->protectString($this->lccode     );
        $this->fullcode      = $this->protectString($this->fullcode   );
        $this->fullnspath    = $this->protectString($this->fullnspath );
        $this->strval        = $this->protectString($this->strval     );
        $this->noDelimiter   = $this->protectString($this->noDelimiter);
        $this->visibility    = $this->protectString($this->visibility );

        $this->alternative   = $this->alternative ? 1 : null;
        $this->reference     = $this->reference ? 1 : null;
        $this->heredoc       = $this->heredoc ? 1 : null;
        $this->variadic      = $this->variadic ? 1 : null;
        $this->final         = $this->final ? 1 : null;
        $this->abstract      = $this->abstract ? 1 : null;
        $this->readonly      = $this->readonly ? 1 : null;
        $this->static        = $this->static ? 1 : null;
        $this->absolute      = $this->absolute ? 1 : null;
        $this->constant      = $this->constant ? 1 : null;
        $this->boolean       = $this->boolean ? 1 : null;
        $this->enclosing     = $this->enclosing ? 1 : null;
        $this->bracket       = $this->bracket ? 1 : null;
        $this->flexible      = $this->flexible ? 1 : null;
        $this->close_tag     = $this->close_tag ? 1 : null;

        if ($this->intval > 2147483647) {
            $this->intval = 2147483647;
        }
        if ($this->intval < -2147483648) {
            $this->intval = -2147483648;
        }

        $this->globalvar     = !$this->globalvar ? null : $this->globalvar;

        return (array) $this;
    }

    public function toGraphsonLine(int &$id): stdClass {
        $integerValues = array('args_max',
                               'args_min',
                               'count',
                               'intval',
                               );

        $falseValues = array('globalvar',
                             );

        $properties = array();

        // The array list the properties that will be kept (except for default)
        $atomsValues = array('Sequence' => array('code'        => 0,
                                                 'line'        => 0,
                                                 'position'    => 0,
                                                 'count'       => 0,
                                                 'fullcode'    => 0,
                                                 'rank'        => 0,
                                                 'ws'          => 0,
                                                 ),

                             // This one is used to skip the values configure
                             'to_skip'  => array('id'          => 0,
                                                 'atom'        => 0,
                                                 'noscream'    => 0,
                                                 'reference'   => 0,
                                                 'variadic'    => 0,
                                                 'heredoc'     => 0,
                                                 'flexible'    => 0,
                                                 'constant'    => 0,
                                                 'enclosing'   => 0,
                                                 'final'       => 0,
                                                 'boolean'     => 0,
                                                 'bracket'     => 0,
                                                 'close_tag'   => 0,
                                                 'trailing'    => 0,
                                                 'alternative' => 0,
                                                 'absolute'    => 0,
                                                 'abstract'    => 0,
                                                 'readonly'    => 0,
                                                 'isRead'      => 0,
                                                 'isModified'  => 0,
                                                 'static'      => 0,
                                                 'isNull'      => 0,
                                                 'isPhp'       => 0,
                                                 'isExt'       => 0,
                                                 'isStub'      => 0,
                               )
                            );

        if (isset($atomsValues[$this->atom])) {
            $list = array_intersect_key((array) $this, $atomsValues[$this->atom]);
        } else {
            $list = array_diff_key((array) $this, $atomsValues['to_skip']);
        }

        foreach($list as $l => $value) {
            if ($value === null) { continue; }

            if (in_array($l, $falseValues) &&
                !$value) {
                continue;
            }

            if ($l === 'ws') {
                $value = $this->ws->toJson();
            }

            if ($l === 'lccode') {
                $this->lccode = mb_strtolower((string) $this->code);
                $value = $this->lccode;
            }

            if (!in_array($l, array('noDelimiter', 'lccode', 'code', 'fullcode', 'ws')) &&
                $value === '') {
                continue;
            }

            if ($value === false) {
                continue;
            }

            if (in_array($l, $integerValues)) {
                $value = (integer) $value;
            }

            $properties[$l] = array( new Property($id++, $value) );
        }

        $object = array('id'         => $this->id,
                        'label'      => $this->atom,
                        'inE'        => new \stdClass(),
                        'outE'       => new \stdClass(),
                        'properties' => $properties,
                        );

        return (object) $object;
    }

    public function boolProperties(): array {
        $return = array();
        foreach(array(
                 'noscream',
                 'reference',
                 'variadic',
                 'heredoc',
                 'flexible',
                 'constant',
                 'enclosing',
                 'final',
                 'boolean',
                 'bracket',
                 'close_tag',
                 'trailing',
                 'alternative',
                 'absolute',
                 'abstract',
                 'readonly',
                 'isRead',
                 'isModified',
                 'static',
                 'isNull',
                 'isPhp',
                 'isExt',
                 'isStub',
                               ) as $property) {
            if ($this->$property == true) {
                $return[] = $property;
            }
        }

        return $return;
    }

    private function protectString(string $code): string {
        return addcslashes($code , '\\"');
    }

    public function isA(array $atoms): bool {
        return in_array($this->atom, $atoms, \STRICT_COMPARISON);
    }
}

?>
