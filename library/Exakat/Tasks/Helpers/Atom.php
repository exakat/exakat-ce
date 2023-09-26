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
use const STRICT_COMPARISON;

class Atom extends AtomInterface {
    public const STRING_MAX_SIZE = 500;

    public function __construct(int $id, string $atom, int $line, string $ws = '') {
        parent::__construct($ws);
        $this->id   = $id;
        $this->eId  = $id;
        $this->atom = $atom;
        $this->line = $line;
    }

    public function __set(string $name, mixed $value) {
        die("Fatal error : trying to set '$name' property on " . self::class);
    }

    public function __get(string $name): mixed {
        die("Fatal error : trying to get '$name' property on " . self::class);
    }

    public function toGraphsonLine(int &$id, bool $withWs = self::WITHOUT_WS): stdClass {
        $integerValues = array('args_max',
                               'args_min',
                               'count',
                               'intval',
                               'boolean',
                               );

        $falseValues = array('globalvar',
                             );

        $properties = array();

        // The array list the properties that will be kept (except for default)
        $atomsValues = array('Sequence' => array('line'        => 0,
                                                 'code'  	   => 0,
                                                 'rank'        => 0,
                                                 'position'    => 0,
                                                 'count'       => 0,
                                                 'eId'         => 0,
                                                 ),

                            'Comparison'=> array('line'        => 0,
                                                 'intval'      => 0,
                                                 'constant'    => 0,
                                                 'rank'        => 0,
                                                 'noDelimiter' => 0,
                                                 'boolean'     => 0,
                                                 'fullcode'    => 0,
                                                 'code'  	   => 0,
                                                 'token'       => 0,
                                                 'eId'         => 0,
                                                 ),

                            'Spaceship' => array('fullcode'    => 0,
                                                 'boolean'     => 0,
                                                 'constant'    => 0,
                                                 'code'  	   => 0,
                                                 'intval'      => 0,
                                                 'noDelimiter' => 0,
                                                 'line'        => 0,
                                                 'rank'        => 0,
                                                 'token'       => 0,
                                                 'eId'         => 0,
                                                 ),


                            'Class'     => array('line'        => 0,
                                                 'count'       => 0,
                                                 'code'        => 0,
                                                 'fullcode'    => 0,
                                                 'fullnspath'  => 0,
                                                 'rank'  	   => 0,
                                                 'eId'         => 0,
                                                 ),

                            'Interface' => array('line'        => 0,
                                                 'code'        => 0,
                                                 'rank'        => 0,
                                                 'fullcode'    => 0,
                                                 'fullnspath'  => 0,
                                                 'eId'         => 0,
                                                 ),

                            'Trait'     => array('line'        => 0,
                                                 'code'        => 0,
                                                 'fullcode'    => 0,
                                                 'fullnspath'  => 0,
                                                 'rank'  	   => 0,
                                                 'eId'         => 0,
                                                 ),

                            'Function'  => array('line'        => 0,
                                                 'count'       => 0,
                                                 'fullcode'    => 0,
                                                 'typehint'    => 0,
                                                 'code'        => 0,
                                                 'args_max'    => 0,
                                                 'args_min'    => 0,
                                                 'fullnspath'  => 0,
                                                 'rank'  	   => 0,
                                                 'eId'         => 0,
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
                                                 'isConst'     => 0,

                            // permanant exclusion
                                                 'position'	   => 0,
                               ),
                            );

        // for cobbler only
        if ($withWs === self::WITH_WS) {
            foreach ($atomsValues as &$value) {
                $value['ws'] = 0;
            }
            unset($value);
            unset($atomsValues['to_skip']['ws']);
        } else {
            $atomsValues['to_skip']['ws'] = 0;
        }

        if (isset($atomsValues[$this->atom])) {
            $list = array_intersect_key((array) $this, $atomsValues[$this->atom]);
        } else {
            $list = array_diff_key((array) $this, $atomsValues['to_skip']);
        }

        foreach ($list as $l => $value) {
            if ($value === null) {
                continue;
            }

            if (in_array($l, $falseValues) &&
                !$value) {
                continue;
            }

            if ($l === 'ws') {
                $value = $this->ws->toJson();
            }

            if (!in_array($l, array('noDelimiter', 'fullcode', 'ws')) &&
                $value === '') {
                continue;
            }

            if (in_array($l, $integerValues)) {
                $value = (integer) $value;
            }

            if ($value === false) {
                continue;
            }

            if ($l === 'visibility' && empty($value)) {
                continue;
            }

            $properties[$l] = array( new Property($id++, $value) );
        }

        if (isset($properties['code'])) {
            $properties['lccode'] = array( new Property($id++, mb_strtolower((string) $this->code)) );
        }

        $object = array('id'         => $this->id,
                        'label'      => $this->atom,
                        'inE'        => new stdClass(),
                        'outE'       => new stdClass(),
                        'properties' => $properties,
                        );

        return (object) $object;
    }

    public function properties(): array {
        return array_keys(array_filter((array) $this));
    }

    public function boolProperties(): array {
        $return = array();
        foreach (array(
                 'noscream',
                 'reference',
                 'variadic',
                 'heredoc',
                 'flexible',
                 'constant',
                 'enclosing',
                 'final',
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
                 'isConst',
                               ) as $property) {
            if ($this->$property === true) {
                $return[] = $property;
            }
        }

        return $return;
    }

    public function isA(array $atoms): bool {
        return in_array($this->atom, $atoms, STRICT_COMPARISON);
    }
}

?>
