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


namespace Exakat\Query\DSL;

use Exakat\Exceptions\DSLException;
use Exakat\Tasks\Helpers\Atom;
use Exakat\GraphElements;
use Exakat\Analyzer\Analyzer;
use Exakat\Data\Dictionary;

abstract class DSL {
    public const VARIABLE_WRITE = true;
    public const VARIABLE_READ  = false;

    public const LABEL_SET  = true;
    public const LABEL_GO   = false;

    public const LEVELS_TO_ANALYSE = 4;

    public const PROPERTIES = array('id',
                             'atom',
                             'code',
                             'lccode',
                             'fullcode',
                             'line',
                             'token',
                             'rank',
                             'alternative',
                             'reference',
                             'heredoc',
                             'delimiter',
                             'noDelimiter',
                             'variadic',
                             'count',
                             'fullnspath',
                             'absolute',
                             'alias',
                             'origin',
                             'encoding',
                             'block',
                             'intval',
                             'strval',
                             'boolean',
                             'enclosing',
                             'args_max',
                             'args_min',
                             'bracket',
                             'flexible',
                             'close_tag',
                             'aliased',
                             'propertyname',
                             'constant',
                             'root',
                             'globalvar',
                             'binaryString',
                             'isNull',
                             'visibility',
                             'final',
                             'abstract',
                             'static',
                             'noscream',
                             'trailing',
                             'isPhp',
                             'isExt',
                             'isStub',
                             'typehint',
                             'isConst',
                             );

    protected const BOOLEAN_PROPERTY = array('abstract',
                                             'absolute',
                                             'aliased',
                                             'alternative',
                                             'bracket',
                                             'constant',
                                             'enclosing',
                                             'final',
                                             'heredoc',
                                             'isModified',
                                             'isRead',
                                             'noscream',
                                             'reference',
                                             'static',
                                             'trailing',
                                             'variadic',
                                             'isPhp',
                                             'isExt',
                                             'isStub',
                                             'extra',
                                             );

    protected const INTEGER_PROPERTY = array('line',
                                             'rank',
                                             'propertyname',
                                             'boolean',
                                             'count',
                                             'code',
                                             'lccode',
                                             );

    protected DSLFactory $dslfactory             ;
    protected array      $availableAtoms         = array();
    protected array      $availableLinks         = array();
    protected array      $availableFunctioncalls = array();
    protected array      $availableProperties    = array();
    protected array      $availableVariables     = array(); // This one is per query
    protected array      $availableLabels        = array(); // This one is per query
    protected Dictionary $dictCode               ;
    protected array      $ignoredcit             = array();
    protected array      $ignoredfunctions       = array();
    protected array      $ignoredconstants       = array();
    protected array      $dependsOn              = array();
    protected string     $analyzerQuoted         = '';

    protected static string $linksDown     = '';
    protected static int    $MAX_LOOPING   = Analyzer::MAX_LOOPING;
    protected static int    $MAX_SEARCHING = Analyzer::MAX_SEARCHING;
    protected static int    $TIME_LIMIT    = Analyzer::TIME_LIMIT;
    protected static array  $ATOMS         = array();
    protected static array  $LINKS         = array();

    public function __construct(DSLFactory $dslfactory,
        array $availableAtoms         = array(),
        array $availableLinks         = array(),
        array $availableFunctioncalls = array(),
        array &$availableVariables    = array(),
        array &$availableLabels       = array(),
        array $ignoredcit             = array(),
        array $ignoredfunctions       = array(),
        array $ignoredconstants       = array(),
        array $dependsOn              = array(),
        string $analyzerQuoted        = ''
    ) {
        $this->dslfactory             = $dslfactory;
        $this->dictCode 			  = Dictionary::getInstance();
        $this->availableAtoms         = $availableAtoms;
        $this->availableLinks         = $availableLinks;
        $this->availableFunctioncalls = $availableFunctioncalls;
        $this->availableVariables     = &$availableVariables;
        $this->availableLabels        = &$availableLabels;
        $this->ignoredcit             = $ignoredcit;
        $this->ignoredfunctions       = $ignoredfunctions;
        $this->ignoredconstants       = $ignoredconstants;
        $this->dependsOn              = $dependsOn;
        $this->analyzerQuoted         = $analyzerQuoted;

        $this->availableProperties = GraphElements::PROPERTIES;

        if (empty(self::$linksDown)) {
            self::$linksDown = GraphElements::linksDownAsList();
        }

        if (empty(self::$ATOMS)) {
            self::$ATOMS = array_merge(GraphElements::$ATOMS, GraphElements::$ATOMS_EXAKAT);
        }

        if (empty(self::$LINKS)) {
            self::$LINKS = array_merge(GraphElements::$LINKS, GraphElements::$LINKS_EXAKAT);
        }
    }

    abstract public function run(): Command;

    protected function normalizeAtoms(string|array $atoms): array {
        $atoms = makeArray($atoms);
        return array_values(array_intersect($atoms, $this->availableAtoms));
    }

    protected function normalizeLinks(string|array $links): array {
        $links = makeArray($links);
        return array_values(array_intersect($links, $this->availableLinks));
    }

    protected function normalizeProperties(string|array $properties): array {
        $properties = makeArray($properties);
        return array_values(array_intersect($properties, $this->availableProperties));
    }

    protected function normalizeFunctioncalls(string|array $fullnspaths): array {
        $fullnspaths = makeArray($fullnspaths);
        return array_values(array_intersect($fullnspaths, $this->availableFunctioncalls));
    }

    protected function SorA(array|string $value): string {
        if (is_array($value)) {
            return makeList($value);
        } else {
            return '"' . $value . '"';
        }
    }

    protected function isLabel(string $name): bool {
        return in_array($name, $this->availableLabels, STRICT_COMPARISON);
    }

    protected function assertLabel(string|array $name, bool $read = self::LABEL_GO): bool {
        if (is_array($name)) {
            foreach ($name as $n) {
                $this->assertLabel($n, $read);
            }
            return true;
        }

        if ($read === self::LABEL_SET) {
            assert(!in_array($name, $this->availableLabels, STRICT_COMPARISON), "Label '$name' is already set : " . join(', ', $this->availableLabels));
            $this->availableLabels[] = $name;
        } else {
            assert(in_array($name, $this->availableLabels, STRICT_COMPARISON), "Label '$name' is not set");
        }
        return true;
    }

    protected function isVariable(string $name): bool {
        return in_array($name, $this->availableVariables, STRICT_COMPARISON);
    }

    protected function assertVariable(string $name, bool $write = self::VARIABLE_READ): bool {
        if ($write === self::VARIABLE_WRITE) {
            assert(!$this->isVariable($name), "Variable '$name' is already taken : " . print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true) . PHP_EOL . print_r($this, true));
            assert(!in_array($name, self::PROPERTIES, STRICT_COMPARISON), "Don't use a property name as a variable ($name)");
            $this->availableVariables[] = $name;
        } else {
            assert($this->isVariable($name), "Variable '$name' is not defined");
        }
        return true;
    }

    protected function assertLink(string|array $link): bool {
        if (is_string($link)) {
            if (in_array($link, array('KEY', 'ELEMENT', 'PROPERTY'), STRICT_COMPARISON) ) {
                throw new DSLException("$link is no more", self::LEVELS_TO_ANALYSE);
            }
            if ($link !== strtoupper($link)) {
                throw new DSLException("Wrong format for LINK name : $link", self::LEVELS_TO_ANALYSE);
            }
            if (preg_match('/[^A-Z]/', $link)) {
                throw new DSLException("Not a link : $link", self::LEVELS_TO_ANALYSE);
            }
            assert(in_array($link, self::$LINKS, STRICT_COMPARISON), "No such link as '$link'");
        } else {
            foreach ($link as $l) {
                $this->assertLink($l);
                assert(in_array($l, self::$LINKS, STRICT_COMPARISON), "No such link as '$l'");
            }
        }

        return true;
    }

    protected function assertTokens(string|array $token): bool {
        if (is_string($token)) {
            assert(substr($token, 0, 2) === 'T_', "Wrong prefix for TOKEN name : $token");
            assert($token === strtoupper($token), "Wrong format for TOKEN name : $token");
        } else {
            foreach ($token as $t) {
                assert(substr($t, 0, 2) === 'T_', "Wrong prefix for TOKEN name : $t");
                assert($t === strtoupper($t), "Wrong format for TOKEN name : $t");
            }
        }

        return true;
    }

    protected function assertAtom(string|array $atom): bool {
        if (is_string($atom)) {
            assert($atom === ucfirst(strtolower($atom)), "Wrong format for Atom name : $atom");
            assert(in_array($atom, self::$ATOMS, STRICT_COMPARISON), "No such atom as '$atom'");
        } else {
            foreach ($atom as $a) {
                assert($a === ucfirst(strtolower($a)), "Wrong format for Atom name : $a");
                assert(in_array($a, self::$ATOMS, STRICT_COMPARISON), "No such atom as '$a'");
            }
        }

        return true;
    }

    protected function assertAnalyzer(string|array $analyzer): bool {
        if (is_string($analyzer)) {
            assert(preg_match('#^[A-Z]\w+/[A-Z]\w+$#', $analyzer) !== false, "Wrong format for Analyzer : $analyzer");
            assert(class_exists('\\Exakat\\Analyzer\\' . str_replace('/', '\\', $analyzer)), "No such analyzer as $analyzer");
        } else {
            foreach ($analyzer as $a) {
                assert(preg_match('#^[A-Z]\W\w+/[A-Z]\W\w+$#', $a) !== false, "Wrong format for Analyzer : $a");
                assert(class_exists('\\Exakat\\Analyzer\\' . str_replace('/', '\\', $a)), "No such analyzer as $a");
            }
        }

        return true;
    }

    protected function isProperty(string $property): bool {
        return property_exists(Atom::class, $property) || in_array($property, array('typehint', 'label', 'self', 'ignored_dir', 'virtual', 'analyzer', 'propagated', 'isPhp', 'isExt', 'isStub', 'extra'), STRICT_COMPARISON);
    }

    protected function assertProperty(string|array $property): bool {
        if (is_string($property)) {
            assert( ($property === mb_strtolower($property)) || in_array($property, array('noDelimiter', 'isRead', 'isModified', 'isPhp', 'isExt', 'isStub', 'rankName', 'isConst'), STRICT_COMPARISON) , 'Wrong format for property name : "' . $property . '"');
            assert($this->isProperty($property), 'No such property in Atom : "' . $property . '"');
        } elseif (is_array($property)) {
            $properties = $property;
            foreach ($properties as $property) {
                assert( ($property === mb_strtolower($property)) || in_array($property, array('noDelimiter', 'isRead', 'isModified', 'isPhp', 'isExt', 'isStub', 'isConst'), STRICT_COMPARISON), "Wrong format for property name : '$property'");
                assert($this->isProperty($property), "No such property in Atom : '$property'");
            }
        } else {
            assert(false, 'Unsupported type for property : ' . gettype($property));
        }
        return true;
    }

    protected function cleanAnalyzerName(string $gremlin, array $dependencies = array()): string {
        $fullNames = array_map($this->makeBaseName(...), $dependencies);

        return str_replace($dependencies, $fullNames, $gremlin);
    }

    public static function makeBaseName(string $className): string {
        // No Exakat, no Analyzer, using / instead of \
        return $className;
    }

    protected function tolowercase(string|array $code): string|array {
        if (is_array($code)) {
            $code = array_map('mb_strtolower', $code);
        } elseif (is_string($code)) {
            $code = mb_strtolower($code);
        } else {
            assert(false, __METHOD__ . ' received an unprocessable object ' . gettype($code));
        }

        return $code;
    }

    protected function makeLinks(array|string $links, string $direction = 'in'): string {
        if (empty($links)) {
            return '.out( )';
        }

        $return = array();

        $links = makeArray($links);
        foreach ($links as $l) {
            if (empty($l)) {
                $return[] = ".$direction( )";
            } elseif (is_array($l)) {
                $list = implode('", "', $l);
                $return[] = ".$direction(\"$list\")";
            } elseif (is_string($l)) {
                $return[] = ".$direction(\"$l\")";
            } else {
                assert(false, __METHOD__ . ' received an unprocessable object ' . gettype($l));
            }
        }

        return implode('', $return);
    }

    protected function assertArguments(int $maxCount, int $actualCount, string $method): void {
        assert($actualCount === $maxCount,
            'Wrong number of argument for ' . $method . '. ' . $maxCount . ' is expected, ' . $actualCount . ' provided.');
    }

    protected function protectValue(string|array|bool|int|null $value): string {
        if (is_string($value)) {
            if ($this->isVariable($value)) {
                return $value;
            } else {
                return '"' . addcslashes($value, '$"\'\\') . '"';
            }
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        assert(false, 'Could not process value type : ' . gettype($value));
    }
}

?>
