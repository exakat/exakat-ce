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


class IsStub extends Plugin {
    public $name = 'isStub';
    public $type = 'boolean';
    private $stubConstants        = array();
    private $stubFunctions        = array();
    private $stubClasses          = array();
    private $stubInterfaces       = array();
    private $stubTraits           = array();

    private $stubClassConstants   = array();
    private $stubClassMethods     = array();
    private $stubClassProperties  = array();

    private $stubMethods          = array();
    private $stubProperties       = array();

    public function __construct() {
        parent::__construct();

        $stubs  = exakat('stubs');

        $this->stubConstants = $stubs->getConstantList();
        $this->stubConstants = makeFullNsPath($this->stubConstants, \FNP_CONSTANT);

        $this->stubFunctions = $stubs->getFunctionList();
        $this->stubFunctions = makeFullNsPath($this->stubFunctions);

        $this->stubClasses = $stubs->getClassList();
        $this->stubClasses = makeFullNsPath($this->stubClasses);

        $this->stubInterfaces = $stubs->getInterfaceList();
        $this->stubInterfaces = makeFullNsPath($this->stubInterfaces);

        $this->stubTraits = $stubs->getTraitList();
        $this->stubTraits = makeFullNsPath($this->stubTraits);

        // cases and constants are mixed! NO way to disambiguate them later.
        $this->stubClassConstants   = array_merge($stubs->getClassConstantList(),
                                                  $stubs->getEnumCasesList());
        $this->stubProperties       = $stubs->getClassPropertyList();
        $this->stubStaticProperties = $stubs->getClassStaticPropertyList();
        $this->stubMethods          = $stubs->getClassMethodList();
        $this->stubStaticMethods    = $stubs->getClassStaticMethodList();
    }

    public function run(Atom $atom, array $extras): void {
        $id   = strrpos($atom->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
        $path = substr($atom->fullnspath  ?? self::NOT_PROVIDED, $id);

        switch ($atom->atom) {
            case 'Methodcall' :
                $path = makeFullNsPath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));
                if (in_array($method, $this->stubMethods[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Member' :
                $path = makeFullNsPath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->stubProperties[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Staticclass' :
                $path = makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($path, $this->stubClasses, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                    $extras['CLASS']->isStub = true;
                }
                break;

            case 'Staticmethodcall' :
                $path = makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));
                if (in_array($method, $this->stubClassMethods[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }

                if (in_array($atom->fullnspath ?? self::NOT_PROVIDED, $this->stubStaticMethods ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Staticproperty' :
                $path = makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                // @todo : this won't work, due tu [$path]
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->stubClassProperties[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }

                if (in_array($atom->fullnspath ?? self::NOT_PROVIDED, $this->stubStaticProperties ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Staticconstant' :
                if (in_array($atom->fullnspath ?? self::NOT_PROVIDED, $this->stubClassConstants, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Functioncall' :
                if (empty($path)) {
                    break;
                }

                if (in_array(makeFullNsPath($path), $this->stubFunctions, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }

                if (in_array($atom->fullnspath, $this->stubFunctions, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'String' :
                if (in_array(makeFullNsPath($atom->noDelimiter), $this->stubFunctions, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Ppp' :
            case 'Parameter' :
                $this->checkTypes($extras['TYPEHINT'] ?? array());
                break;

            case 'Usenamespace' :
                foreach($extras as $extra) {
                    $id   = strrpos($extra->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
                    $path = substr($extra->fullnspath ?? self::NOT_PROVIDED, $id);

                    switch($extra->use) {
                        case 'function':
                            if (in_array(makeFullNsPath($path), $this->stubFunctions, \STRICT_COMPARISON)) {
                                $extra->isStub = true;
                            }
                        break;

                        case 'const':
                            if (in_array(makeFullNsPath($path, \FNP_CONSTANT), $this->stubConstants, \STRICT_COMPARISON)) {
                                $extra->isStub = true;
                            }
                        break;

                    default: // case class
                            if (in_array(makeFullNsPath($path), $this->stubClasses, \STRICT_COMPARISON)) {
                                $extra->isStub = true;
                            }
                    }
                }
                break;

            case 'Usetrait' :
                // for specific namespaces
                foreach($extras as $extra) {
                    if (in_array($extra->fullnspath, $this->stubTraits, \STRICT_COMPARISON)) {
                        $extra->isStub = true;
                    }
                }
                break;

            case 'Class' :
                if (isset($extras['ATTRIBUTE'])) {
                    foreach($extras['ATTRIBUTE'] as $extra) {
                        if (in_array($extra->fullnspath ?? self::NOT_PROVIDED, $this->stubClasses, \STRICT_COMPARISON)) {
                            $extra->isStub = true;
                        }
                    }
                }
                // Fallthrough is OK

            //case 'Class' :
            case 'Classanonymous' :
                if (isset($extras['EXTENDS']) &&
                    in_array($extras['EXTENDS']->fullnspath ?? self::NOT_PROVIDED, $this->stubClasses, \STRICT_COMPARISON)) {
                    $extras['EXTENDS']->isStub = true;
                }

                foreach($extras['IMPLEMENTS'] ?? array() as $implements) {
                    if (in_array($implements->fullnspath ?? self::NOT_PROVIDED, $this->stubInterfaces, \STRICT_COMPARISON)) {
                        $implements->isStub = true;
                    }
                }
                break;

            case 'Interface' :
                foreach($extras['EXTENDS'] as $extra) {
                    if (in_array($extra->fullnspath ?? self::NOT_PROVIDED, $this->stubInterfaces, \STRICT_COMPARISON)) {
                        $extra->isStub = true;
                    }
                }
                break;

            case 'Constant' :
                $atom->isStub = false;
                $extras['NAME']->isStub = false;
                break;

            case 'Instanceof' :
                // Warning : atom->fullnspath for classes (no fallback)
                if (in_array(makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED), $this->stubClasses, \STRICT_COMPARISON)) {
                    $extras['CLASS']->isStub = true;
                }
                if (in_array(makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED), $this->stubInterfaces, \STRICT_COMPARISON)) {
                    $extras['CLASS']->isStub = true;
                }
                break;

            case 'Parameter' :
                foreach($extras as $extra) {
                    if (in_array(makeFullNsPath($extra->fullnspath ?? self::NOT_PROVIDED), $this->stubClasses, \STRICT_COMPARISON)) {
                        $extra->isStub = true;
                        $atom->isStub = true;
                    }
                    if (in_array(makeFullNsPath($extra->fullnspath ?? self::NOT_PROVIDED), $this->stubInterfaces, \STRICT_COMPARISON)) {
                        $atom->isStub = true;
                    }
                }
                break;

            case 'Function' :
            case 'Closure' :
            case 'Arrowfunction' :
            case 'Method' :
            case 'Magicmethod' :
                $this->checkTypes($extras['RETURNTYPE'] ?? array());
                break;

            case 'Newcall' :
                if (empty($path)) {
                    break;
                }
                // Warning : atom->fullnspath for classes (no fallback)
                if (in_array(makeFullNsPath($atom->fullnspath), $this->stubClasses, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Newcallname' :
                if (in_array($atom->fullnspath, $this->stubClasses, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Identifier' :
            case 'Nsname' :
                if (empty($path)) {
                    break;
                }

                // for fallback to main namespace
                if (in_array($path, $this->stubConstants, \STRICT_COMPARISON) &&
                    strpos($atom->fullcode, '\\', 1) === false) {
                    $atom->isStub = true;
                }

                // for specific namespaces
                if (in_array($atom->fullnspath, $this->stubConstants, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Catch' :
                foreach($extras as $extra) {
                    $path = $extra->fullnspath ?? self::NOT_PROVIDED;

                    if (in_array(makeFullNsPath($path), $this->stubClasses, \STRICT_COMPARISON)) {
                        $extra->isStub = true;
                    }

                    if (in_array(makeFullNsPath($path), $this->stubInterfaces, \STRICT_COMPARISON)) {
                        $extra->isStub = true;
                    }
                }
                break;

            case 'Isset' :
            case 'Empty' :
            case 'Unset' :
            case 'Exit'  :
            case 'Empty' :
            case 'Echo'  :
            case 'Print' :
                $atom->isStub = false;
                break;

            default :
                // Nothing
        }
    }

    private function checkTypes(array $extras): void {
        foreach($extras as $extra) {
            $id   = strrpos($extra->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
            $path = substr($extra->fullnspath ?? self::NOT_PROVIDED, $id);

            if (in_array(makeFullNsPath($path), $this->stubClasses, \STRICT_COMPARISON)) {
                $extra->isStub = true;
            }
            if (in_array(makeFullNsPath($path), $this->stubInterfaces, \STRICT_COMPARISON)) {
                $extra->isStub = true;
            }

            if (in_array($extra->fullnspath, $this->stubClasses, \STRICT_COMPARISON)) {
                $extra->isStub = true;
            }
            if (in_array($extra->fullnspath, $this->stubInterfaces, \STRICT_COMPARISON)) {
                $extra->isStub = true;
            }
        }
    }
}

?>
