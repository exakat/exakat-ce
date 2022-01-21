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

        $config = exakat('config');
        $stubs  = exakat('stubs');

        $this->stubConstants = $stubs->getConstantList();
        $this->stubConstants = makeFullnspath($this->stubConstants, \FNP_CONSTANT);

        $this->stubFunctions = $stubs->getFunctionList();
        $this->stubFunctions = makeFullnspath($this->stubFunctions);

        $this->stubClasses = $stubs->getClassList();
        $this->stubClasses = makeFullnspath($this->stubClasses);

        $this->stubInterfaces = $stubs->getInterfaceList();
        $this->stubInterfaces = makeFullnspath($this->stubInterfaces);

        $this->stubTraits = $stubs->getTraitList();
        $this->stubTraits = makeFullnspath($this->stubTraits);

        $classConstants = $stubs->getClassConstantList();
        if (!empty($classConstants)) {
            foreach($classConstants as $fullConstant) {
                list($class, $constant) = explode('::', $fullConstant, 2);
                array_collect_by($this->stubClassConstants, makeFullnspath($class), $constant);
            }
        }

        $classProperties = $stubs->getClassPropertyList();
        if (!empty($classProperties)) {
            foreach($classProperties as $property) {
                list($class, $p) = explode('::', $property, 2);
                array_collect_by($this->stubClassProperties, makeFullnspath($class), $p);
            }
        }

        $classMethods = $stubs->getClassMethodList();
        if (!empty($classMethods)) {
            foreach($classMethods as $method) {
                list($class, $m) = explode('::', $method, 2);
                array_collect_by($this->stubClassMethods, makeFullnspath($class), $m);
            }
        }

        $classProperties = $stubs->getPropertyList();
        if (!empty($classProperties)) {
            foreach($classProperties as $property) {
                list($class, $p) = explode('::', $property, 2);
                array_collect_by($this->stubProperties, makeFullnspath($class), $p);
            }
        }

        $classMethods = $stubs->getMethodList();
        if (!empty($classMethods)) {
            foreach($classMethods as $method) {
                list($class, $m) = explode('::', $method, 2);
                array_collect_by($this->stubMethods, makeFullnspath($class), $m);
            }
        }
    }

    public function run(Atom $atom, array $extras): void {
        $id   = strrpos($atom->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
        $path = substr($atom->fullnspath ?? self::NOT_PROVIDED, $id);

        switch ($atom->atom) {
            case 'Methodcall' :
                $path = makeFullnspath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));
                if (in_array($method, $this->stubMethods[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Member' :
                $path = makeFullnspath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->stubProperties[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Staticclass' :
                $path = makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($path, $this->stubClasses, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                    $extras['CLASS']->isStub = true;
                }
                break;

            case 'Staticmethodcall' :
                $path = makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));
                if (in_array($method, $this->stubClassMethods[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Staticproperty' :
                $path = makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->stubClassProperties[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Staticconstant' :
                $path = makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['CONSTANT']->code ?? self::NOT_PROVIDED, $this->stubClassConstants[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                    $extras['CLASS']->isStub = true;
                }
                break;

            case 'Functioncall' :
                if (empty($path)) {
                    break;
                }
                if (in_array(makeFullnspath($path), $this->stubFunctions, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'String' :
                if (in_array(makeFullnspath($atom->noDelimiter), $this->stubFunctions, \STRICT_COMPARISON)) {
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
                            if (in_array(makeFullnspath($path), $this->stubFunctions, \STRICT_COMPARISON)) {
                                $extra->isStub = true;
                            }
                        break;

                        case 'const':
                            if (in_array(makeFullnspath($path, \FNP_CONSTANT), $this->stubConstants, \STRICT_COMPARISON)) {
                                $extra->isStub = true;
                            }
                        break;

                    default: // case class
                            if (in_array(makeFullnspath($path), $this->stubClasses, \STRICT_COMPARISON)) {
                                $extra->isStub = true;
                            }
                    }
                }
                break;

            case 'Class' :
                foreach($extras['ATTRIBUTE'] as $extra) {
                    if (in_array($extra->fullnspath ?? self::NOT_PROVIDED, $this->stubClasses, \STRICT_COMPARISON)) {
                        $extra->isPhp = true;
                    }
                }
                // Fallthrough is OK

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
                    if (in_array($extras['EXTENDS']->fullnspath ?? self::NOT_PROVIDED, $this->stubInterfaces, \STRICT_COMPARISON)) {
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
                if (in_array(makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED), $this->stubClasses, \STRICT_COMPARISON)) {
                    $extras['CLASS']->isStub = true;
                }
                if (in_array(makeFullnspath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED), $this->stubInterfaces, \STRICT_COMPARISON)) {
                    $extras['CLASS']->isStub = true;
                }
                break;

            case 'Parameter' :
                foreach($extras as $extra) {
                    if (in_array(makeFullnspath($extra->fullnspath ?? self::NOT_PROVIDED), $this->stubClasses, \STRICT_COMPARISON)) {
                        $extra->isStub = true;
                        $atom->isStub = true;
                    }
                    if (in_array(makeFullnspath($extra->fullnspath ?? self::NOT_PROVIDED), $this->stubInterfaces, \STRICT_COMPARISON)) {
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
                if (in_array(makeFullnspath($atom->fullnspath), $this->stubClasses, \STRICT_COMPARISON)) {
                    $atom->isStub = true;
                }
                break;

            case 'Identifier' :
            case 'Nsname' :
                if (empty($path)) {
                    break;
                }

                if (in_array($path, $this->stubConstants, \STRICT_COMPARISON) &&
                    strpos($atom->fullcode, '\\', 1) === false) {
                    $atom->isStub = true;
                }
                break;

            case 'Catch' :
                foreach($extras as $extra) {
                    $id   = strrpos($extra->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
                    $path = substr($extra->fullnspath ?? self::NOT_PROVIDED, $id);

                    if (in_array(makeFullnspath($path), $this->stubClasses, \STRICT_COMPARISON)) {
                        $extra->isStub = true;
                    }

                    if (in_array(makeFullnspath($path), $this->stubInterfaces, \STRICT_COMPARISON)) {
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
    
    private function checkTypes(array $extras) : void {
        foreach($extras as $extra) {
            $id   = strrpos($extra->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
            $path = substr($extra->fullnspath ?? self::NOT_PROVIDED, $id);
    
            if (in_array(makeFullnspath($path), $this->stubClasses, \STRICT_COMPARISON)) {
                $extra->isStub = true;
            }
        }
    }
}

?>
