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

use const STRICT_COMPARISON;
use Exakat\Stubs\Stubs;

class IsPhp extends Plugin {
    public $name = 'isPhp';
    public $type = 'boolean';
    private array $phpFunctions        = array();
    private array $phpConstants        = array();
    private array $phpClasses          = array();
    private array $phpInterfaces       = array();
    private array $phpEnums            = array();
    private array $phpClassConstants   = array();
    private array $phpStaticMethods    = array();
    private array $phpStaticProperties = array();
    private array $phpTraits           = array();

    public function __construct() {
        parent::__construct();

        $config = exakat('config');
        $php = new Stubs($config->dir_root . '/data/core/',
            $config->php_core ?? array(),
        );
        $this->phpConstants = $php->getConstantList();
        $this->phpConstants = makeFullNsPath($this->phpConstants, \FNP_CONSTANT);

        $this->phpFunctions = $php->getFunctionList();
        $this->phpFunctions = makeFullNsPath($this->phpFunctions);

        $this->phpClasses = $php->getClassList();
        $this->phpClasses = makeFullNsPath($this->phpClasses);

        $this->phpInterfaces = $php->getInterfaceList();
        $this->phpInterfaces = makeFullNsPath($this->phpInterfaces);

        $this->phpTraits = $php->getTraitList();
        $this->phpTraits = makeFullNsPath($this->phpTraits);

        $this->phpEnums = $php->getEnumList();
        $this->phpEnums = makeFullNsPath($this->phpEnums);

        $this->phpClassConstants        = array_merge($php->getClassConstantList(),
            $php->getEnumCasesList());
        $this->phpStaticProperties      = $php->getClassStaticPropertyList();
        $this->phpStaticMethods         = $php->getClassMethodList();
        $this->phpStaticMethods         = $php->getClassStaticMethodList();
    }

    public function run(Atom $atom, array $extras): void {
        $id = strrpos($atom->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
        $path = substr($atom->fullnspath ?? self::NOT_PROVIDED, $id);

        switch ($atom->atom) {
            case 'Methodcall' :
                $path = makeFullNsPath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));
                if (in_array($method, $this->phpStaticMethods[$path] ?? array(), STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                break;

            case 'Member' :
                $path = makeFullNsPath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->phpStaticProperties[$path] ?? array(), STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                break;

            case 'Staticmethodcall' :
                if (empty($extras)) {
                    break;
                }

                $path = makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));

                if (in_array($path . '::' . $method, $this->phpStaticMethods, STRICT_COMPARISON)) {
                    $extras['CLASS']->isPhp = true;
                    $atom->isPhp = true;
                }
                break;

            case 'Staticclass' :
                $path = makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($path, array_merge($this->phpClasses,
                    $this->phpTraits,
                    $this->phpInterfaces,
                    $this->phpEnums,
                ), STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                    $extras['CLASS']->isPhp = true;
                }
                break;

            case 'Staticproperty' :
                $path = makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                // @todo : this won't work, due tu [$path]
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->phpStaticProperties[$path] ?? array(), STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }

                if (in_array($atom->fullnspath ?? self::NOT_PROVIDED, $this->phpStaticProperties ?? array(), STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                break;

            case 'Staticconstant' :
                if (in_array($atom->fullnspath ?? self::NOT_PROVIDED, $this->phpClassConstants, STRICT_COMPARISON)) {
                    $extras['CLASS']->isPhp = true;
                    $atom->isPhp = true;
                }
                break;

            case 'Functioncall' :
                if (empty($path)) {
                    break;
                }
                if (in_array(makeFullNsPath($path), $this->phpFunctions, STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                break;

            case 'String' :
                if (in_array(makeFullNsPath($atom->noDelimiter), $this->phpFunctions, STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                if (in_array(makeFullNsPath($atom->noDelimiter), $this->phpClasses, STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                break;

            case 'Ppp' :
            case 'Parameter' :
                $this->checkType($extras['TYPEHINT'] ?? array());
                break;

            case 'Usenamespace' :
                foreach ($extras as $extra) {
                    if ($extra->fullnspath === self::NOT_PROVIDED) {
                        continue;
                    }

                    switch ($extra->use) {
                        case 'function':
                            if (in_array(makeFullNsPath($extra->fullnspath), $this->phpFunctions, STRICT_COMPARISON)) {
                                $extra->isPhp = true;
                            }
                            break;

                        case 'const':
                            if (in_array(makeFullNsPath($extra->fullnspath, \FNP_CONSTANT), $this->phpConstants, STRICT_COMPARISON)) {
                                $extra->isPhp = true;
                            }
                            break;

                        default: // case class
                            if (in_array(makeFullNsPath($extra->fullnspath), array_merge($this->phpClasses,
                                $this->phpTraits,
                                $this->phpInterfaces,
                                $this->phpEnums,
                            ), STRICT_COMPARISON)) {
                                $extra->isPhp = true;
                            }
                    }
                }
                break;

            case 'Class' :
                if (isset($extras['ATTRIBUTE'])) {
                    foreach ($extras['ATTRIBUTE'] as $extra) {
                        if (in_array($extra->fullnspath ?? self::NOT_PROVIDED, $this->phpClasses, STRICT_COMPARISON)) {
                            $extra->isPhp = true;
                        }
                    }
                }
                // Fallthrough is OK

            case 'Classanonymous' :
                if (in_array($extras['EXTENDS']->fullnspath ?? self::NOT_PROVIDED, $this->phpClasses, STRICT_COMPARISON)) {
                    $extras['EXTENDS']->isPhp = true;
                }

                foreach ($extras['IMPLEMENTS'] ?? array() as $implements) {
                    if (in_array($implements->fullnspath ?? self::NOT_PROVIDED, $this->phpInterfaces, STRICT_COMPARISON)) {
                        $implements->isPhp = true;
                    }
                }
                break;

            case 'Interface' :
                foreach ($extras['EXTENDS'] as $extra) {
                    if (in_array($extra->fullnspath ?? self::NOT_PROVIDED, $this->phpInterfaces, STRICT_COMPARISON)) {
                        $extra->isPhp = true;
                    }
                }

                break;

            case 'Constant' :
                $atom->isPhp = false;
                $extras['NAME']->isPhp = false;
                break;

            case 'Function' :
            case 'Closure' :
            case 'Arrowfunction' :
            case 'Method' :
            case 'Magicmethod' :
                $this->checkType($extras['RETURNTYPE'] ?? array());
                break;

            case 'Catch' :
                foreach ($extras as $extra) {
                    $path = $extra->fullnspath ?? self::NOT_PROVIDED;

                    if (in_array(makeFullNsPath($path), array_merge($this->phpClasses,
                        $this->phpInterfaces,
                    ), STRICT_COMPARISON)) {
                        $extra->isPhp = true;
                    }

                    if (in_array(makeFullNsPath($path), $this->phpInterfaces, STRICT_COMPARISON)) {
                        $extra->isPhp = true;
                    }
                }
                break;

            case 'Instanceof' :
                // Warning : atom->fullnspath for classes (no fallback)
                if (in_array(makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED), array_merge($this->phpClasses,
                    $this->phpInterfaces,
                    $this->phpEnums,
                ), STRICT_COMPARISON)) {
                    $extras['CLASS']->isPhp = true;
                }
                break;

            case 'Newcallname' :
                if (in_array(makeFullNsPath($atom->fullnspath), $this->phpClasses, STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                break;

            case 'Newcall' :
                if (empty($path)) {
                    break;
                }
                // Warning : atom->fullnspath for classes (no fallback)
                if (in_array(makeFullNsPath($atom->fullnspath), $this->phpClasses, STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                break;

            case 'Identifier' :
            case 'Nsname' :
            case 'As' :
                if (empty($path)) {
                    break;
                }

                if ($atom->use === 'const') {
                    if (in_array($atom->fullnspath, $this->phpConstants, STRICT_COMPARISON)) {
                        $atom->isPhp = true;
                    }
                } elseif ($atom->use === 'function') {
                    if (in_array($path, $this->phpFunctions, STRICT_COMPARISON) &&
                        strpos($atom->fullcode, '\\', 1) === false                ) {
                        $atom->isPhp = true;
                    }
                } elseif ($atom->use === 'class') {
                    $cit = array_merge($this->phpClasses,
                        $this->phpInterfaces,
                        $this->phpTraits,
                        $this->phpEnums,
                    );
                    if (in_array($atom->fullnspath, $cit, STRICT_COMPARISON)) {
                        $atom->isPhp = true;
                    }
                } else {
                    // This is the default behavior
                    if (in_array($path, $this->phpConstants, STRICT_COMPARISON) &&
                        strpos($atom->fullcode, '\\', 1) === false) { // No extra \\, besides the first one
                        $atom->isPhp = true;
                    }
                }

                break;

            case 'Isset' :
            case 'Empty' :
            case 'Unset' :
            case 'Exit'  :
            case 'Echo'  :
            case 'Print' :
                $atom->isPhp = true;
                break;

            default :
                // Nothing
        }
    }

    private function checkType(array $extras): void {
        foreach ($extras as $extra) {
            if (in_array(makeFullNsPath($extra->fullnspath), array_merge($this->phpClasses,
                $this->phpTraits,
                $this->phpEnums,
                $this->phpInterfaces,
            ), STRICT_COMPARISON)) {
                $extra->isPhp = true;
                continue;
            }

            if (!empty($extra->absolute)) {
                continue;
            }
            if (!empty($extra->use)) {
                continue;
            }

            $id   = strrpos($extra->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
            $path = substr($extra->fullnspath ?? self::NOT_PROVIDED, $id);

            if (in_array(makeFullNsPath($path), array_merge($this->phpClasses,
                $this->phpTraits,
                $this->phpEnums,
                $this->phpInterfaces,
            ), STRICT_COMPARISON)) {
                $extra->isPhp = true;
            }
        }
    }
}

?>
