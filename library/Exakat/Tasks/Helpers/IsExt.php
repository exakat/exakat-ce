<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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

use Exakat\Helpers\Definitions;

class IsExt extends Plugin {
    public $name = 'isExt';
    public $type = 'boolean';
    private $extFunctions        = array();
    private $extConstants        = array();
    private $extClasses          = array();
    private $extInterfaces       = array();
    private $extClassConstants   = array();
    private $extClassMethods     = array();
    private $extClassProperties  = array();
    private $extMethods          = array();
    private $extProperties       = array();

    public function __construct() {
        parent::__construct();

        $config = exakat('config');
        $rulesets = exakat('rulesets');

        $exts = $rulesets->listAllAnalyzer('Extensions');

        $constants = array(array());
        $classes   = array(array());
        $functions = array(array());

        foreach($config->php_extensions ?? array() as $inifile) {
            if ($inifile === 'Extensions/Extstandard') {
                continue;
            }
            $definitions = new Definitions($config, $inifile);

            if (!$definitions->isValid()) {
                continue;
            }

            if (!empty($definitions->get(Definitions::CONSTANTS))) {
                $constants[] = makeFullnspath($definitions->get(Definitions::CONSTANTS), \FNP_CONSTANT);
            }

            // Called class, handling CIT
            if (!empty($definitions->get(Definitions::CLASSES))) {
                $classes[] = makeFullnspath($definitions->get(Definitions::CLASSES), \FNP_NOT_CONSTANT);
            }
            if (!empty($definitions->get(Definitions::TRAITS))) {
                $classes[] = makeFullnspath($definitions->get(Definitions::TRAITS), \FNP_NOT_CONSTANT);
            }
            if (!empty($definitions->get(Definitions::INTERFACES))) {
                $classes[] = makeFullnspath($definitions->get(Definitions::INTERFACES), \FNP_NOT_CONSTANT);
            }

            if (!empty($definitions->get(Definitions::FUNCTIONS))) {
                $functions[] = makeFullnspath($definitions->get(Definitions::FUNCTIONS), \FNP_NOT_CONSTANT);
            }

            if (!empty($definitions->get(Definitions::METHODS))) {
                foreach(array_filter($definitions->get(Definitions::METHODS)) as $fullMethod) {
                    list($class, $method) = explode('::', $fullMethod, 2);
                    array_collect_by($this->extMethods, makeFullnspath($class),  mb_strtolower($method));
                }
            }

            if (!empty($definitions->get(Definitions::PROPERTIES))) {
                foreach(array_filter($definitions->get(Definitions::PROPERTIES)) as $fullProperty) {
                    list($class, $property) = explode('::', $fullProperty, 2);
                    array_collect_by($this->extProperties, makeFullnspath($class), ltrim($property, '$'));
                }
            }

            if (!empty($definitions->get(Definitions::STATIC_METHODS))) {
                foreach(array_filter($definitions->get(Definitions::STATIC_METHODS)) as $fullMethod) {
                    list($class, $method) = explode('::', $fullMethod, 2);
                    array_collect_by($this->extClassMethods, makeFullnspath($class),  mb_strtolower($method));
                }
            }

            if (!empty($definitions->get(Definitions::STATIC_PROPERTIES))) {
                foreach(array_filter($definitions->get(Definitions::STATIC_PROPERTIES)) as $fullProperty) {
                    list($class, $property) = explode('::', $fullProperty, 2);
                    array_collect_by($this->extClassProperties, makeFullnspath($class), $property);
                }
            }

            if (!empty($definitions->get(Definitions::STATIC_CONSTANTS))) {
                foreach(array_filter($definitions->get(Definitions::STATIC_CONSTANTS)) as $fullConstant) {
                    list($class, $constant) = explode('::', $fullConstant, 2);
                    array_collect_by($this->extClassConstants, makeFullnspath($class), $constant);
                }
            }
        }

        // Not doint $o->p and $o->m() ATM : needs $o's type.

        $this->extConstants = array_merge(...$constants);
        $this->extFunctions = array_merge(...$functions);
        $this->extClasses   = array_filter(array_merge(...$classes));
    }

    public function run(Atom $atom, array $extras): void {
        $id   = strrpos($atom->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
        $path = substr($atom->fullnspath ?? self::NOT_PROVIDED, $id);

        switch ($atom->atom) {
            case 'Methodcall' :
                $path = makeFullnspath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));
                if (in_array($method, $this->extMethods[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Member' :
                $path = makeFullnspath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->extProperties[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Staticclass' :
                $path = makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($path, $this->extClasses, \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                    $extras['CLASS']->isExt = true;
                }
                break;

            case 'Staticmethodcall' :
                $path = makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));
                if (in_array($method, $this->extClassMethods[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Staticproperty' :
                $path = makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->extClassProperties[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Staticconstant' :
                $path = makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['CONSTANT']->code ?? self::NOT_PROVIDED, $this->extClassConstants[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                    $extras['CLASS']->isExt = true;
                }
                break;

            case 'Functioncall' :
                if (empty($path)) {
                    break;
                }
                if (in_array(makeFullnspath($path), $this->extFunctions, \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'String' :
                if (in_array(makeFullnspath($atom->noDelimiter), $this->extFunctions, \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Usenamespace' :
            case 'Ppp' :
                foreach($extras as $extra) {
                    $id   = strrpos($extra->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
                    $path = substr($extra->fullnspath ?? self::NOT_PROVIDED, $id);

                    switch($extra->use) {
                        case 'function':
                            if (in_array(makeFullnspath($path), $this->extFunctions, \STRICT_COMPARISON)) {
                                $extra->isExt = true;
                            }
                        break;

                        case 'const':
                            if (in_array(makeFullnspath($path, \FNP_CONSTANT), $this->extConstants, \STRICT_COMPARISON)) {
                                $extra->isExt = true;
                            }
                        break;

                    default: // case class
                            if (in_array(makeFullnspath($path), $this->extClasses, \STRICT_COMPARISON)) {
                                $extra->isExt = true;
                            }
                    }
                }
                break;

            case 'Class' :
                foreach($extras['ATTRIBUTE'] as $extra) {
                    if (in_array($extra->fullnspath ?? self::NOT_PROVIDED, $this->extClasses, \STRICT_COMPARISON)) {
                        $extra->isPhp = true;
                    }
                }
                // Fallthrough is OK

            case 'Classanonymous' :
                if (isset($extras['EXTENDS']) &&
                    in_array($extras['EXTENDS']->fullnspath ?? self::NOT_PROVIDED, $this->extClasses, \STRICT_COMPARISON)) {
                    $extras['EXTENDS']->isExt = true;
                }

                foreach($extras['IMPLEMENTS'] ?? array() as $implements) {
                    if (in_array($implements->fullnspath ?? self::NOT_PROVIDED, $this->extInterfaces, \STRICT_COMPARISON)) {
                        $implements->isExt = true;
                    }
                }
                break;

            case 'Interface' :
                foreach($extras['ATTRIBUTE'] as $extra) {
                    if (in_array($extra->fullnspath ?? self::NOT_PROVIDED, $this->extInterfaces, \STRICT_COMPARISON)) {
                        $extra->isExt = true;
                    }
                }

                foreach($extras['EXTENDS'] as $extra) {
                    if (in_array($extras['EXTENDS']->fullnspath ?? self::NOT_PROVIDED, $this->extInterfaces, \STRICT_COMPARISON)) {
                        $extra->isExt = true;
                    }
                }
                break;

            case 'Trait' :
                foreach($extras['ATTRIBUTE'] as $extra) {
                    if (in_array($extra->fullnspath ?? self::NOT_PROVIDED, $this->extInterfaces, \STRICT_COMPARISON)) {
                        $extra->isExt = true;
                    }
                }
                break;

            case 'Constant' :
                $atom->isExt = false;
                $extras['NAME']->isExt = false;
                break;

            case 'Instanceof' :
                // Warning : atom->fullnspath for classes (no fallback)
                if (in_array(makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED), $this->extClasses, \STRICT_COMPARISON)) {
                    $extras['CLASS']->isExt = true;
                }
                if (in_array(makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED), $this->extInterfaces, \STRICT_COMPARISON)) {
                    $extras['CLASS']->isExt = true;
                }
                break;

            case 'Parameter' :
                foreach($extras as $extra) {
                    if (in_array(makeFullnspath($extra->fullnspath ?? self::NOT_PROVIDED), $this->extClasses, \STRICT_COMPARISON)) {
                        $extra->isExt = true;
                        $atom->isExt = true;
                    }
                    if (in_array(makeFullnspath($extra->fullnspath ?? self::NOT_PROVIDED), $this->extInterfaces, \STRICT_COMPARISON)) {
                        $atom->isExt = true;
                    }
                }
                break;

            case 'Function' :
            case 'Closure' :
            case 'Arrowfunction' :
                foreach($extras as $extra) {
                    $id   = strrpos($extra->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
                    $path = substr($extra->fullnspath ?? self::NOT_PROVIDED, $id);

                    if (in_array(makeFullnspath($path), $this->extClasses, \STRICT_COMPARISON)) {
                        $extra->isExt = true;
                    }
                }
                break;

            case 'Newcall' :
            case 'Newcallname' :
                if (empty($path)) {
                    break;
                }
                // Warning : atom->fullnspath for classes (no fallback)
                if (in_array(makeFullnspath($atom->fullnspath), $this->extClasses, \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Identifier' :
            case 'Nsname' :
                if (empty($path)) {
                    break;
                }
                if (in_array($path, $this->extConstants, \STRICT_COMPARISON) &&
                    strpos($atom->fullcode, '\\', 1) === false) {
                    $atom->isExt = true;
                }
                break;

            case 'Catch' :
                foreach($extras as $extra) {
                    $id   = strrpos($extra->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
                    $path = substr($extra->fullnspath ?? self::NOT_PROVIDED, $id);

                    if (in_array(makeFullnspath($path), $this->extClasses, \STRICT_COMPARISON)) {
                        $extra->isExt = true;
                    }

                    if (in_array(makeFullnspath($path), $this->extInterfaces, \STRICT_COMPARISON)) {
                        $extra->isExt = true;
                    }
                }
                break;

            case 'Isset' :
            case 'Isset' :
            case 'Empty' :
            case 'Unset' :
            case 'Exit'  :
            case 'Empty' :
            case 'Echo'  :
            case 'Print' :
                $atom->isExt = false;
                break;

            default :
                // Nothing
        }
    }
}

?>
