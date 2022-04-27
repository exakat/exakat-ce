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


class IsExt extends Plugin {
    public $name = 'isExt';
    public $type = 'boolean';
    private array $extFunctions        = array();
    private array $extConstants        = array();
    private array $extClasses          = array();
    private array $extInterfaces       = array();
    private array $extTraits           = array();
    private array $extEnums            = array();
    private array $extClassConstants   = array();
    private array $extClassMethods     = array();
    private array $extClassProperties  = array();
    private array $extMethods          = array();
    private array $extProperties       = array();

    public function __construct() {
        parent::__construct();

        $ext = exakat('phpExtensions');
        $this->extConstants = $ext->getConstantList();
        $this->extConstants = makeFullNsPath($this->extConstants, \FNP_CONSTANT);

        $this->extFunctions = $ext->getFunctionList();
        $this->extFunctions = makeFullNsPath($this->extFunctions);

        $this->extClasses = $ext->getClassList();
        $this->extClasses = makeFullNsPath($this->extClasses);

        $this->extInterfaces = $ext->getInterfaceList();
        $this->extInterfaces = makeFullNsPath($this->extInterfaces);

        $this->extTraits = $ext->getTraitList();
        $this->extTraits = makeFullNsPath($this->extTraits);

        $this->extEnums = $ext->getEnumList();
        $this->extEnums = makeFullNsPath($this->extEnums);

        $this->extClassConstants = array_merge($ext->getClassConstantList(),
                                               $ext->getEnumCasesList());
        $this->extProperties     = $ext->getClassPropertyList();
        $this->extMethods        = $ext->getClassMethodList();
    }

    public function run(Atom $atom, array $extras): void {
        $id   = strrpos($atom->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
        $path = substr($atom->fullnspath ?? self::NOT_PROVIDED, $id);

        switch ($atom->atom) {
            case 'Methodcall' :
                $path = makeFullNsPath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));
                if (in_array($method, $this->extMethods[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Member' :
                $path = makeFullNsPath($extras['OBJECT']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->extProperties[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Staticclass' :
                $path = makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($path, array_merge($this->extClasses,
                                                $this->extTraits,
                                                $this->extEnums,
                                                $this->extInterfaces,
                                                ), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                    $extras['CLASS']->isExt = true;
                }
                break;

            case 'Staticmethodcall' :
                $path = makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                $method = mb_strtolower(substr($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, 0, strpos($extras['METHOD']->fullcode ?? self::NOT_PROVIDED, '(') ?: 0));
                if (in_array($method, $this->extClassMethods[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Staticproperty' :
                $path = makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED);
                if (in_array($extras['MEMBER']->code ?? self::NOT_PROVIDED, $this->extClassProperties[$path] ?? array(), \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'Staticconstant' :
                if (in_array($atom->fullnspath ?? self::NOT_PROVIDED, $this->extClassConstants, \STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                break;

            case 'Functioncall' :
                if (empty($path)) {
                    break;
                }
                if (in_array(makeFullNsPath($path), $this->extFunctions, \STRICT_COMPARISON)) {
                    $atom->isExt = true;
                }
                break;

            case 'String' :
                if (in_array(makeFullNsPath($atom->noDelimiter), $this->extFunctions, \STRICT_COMPARISON)) {
                    $atom->isExt = true;
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
                            if (in_array(makeFullNsPath($path), $this->extFunctions, \STRICT_COMPARISON)) {
                                $extra->isExt = true;
                            }
                        break;

                        case 'const':
                            if (in_array(makeFullNsPath($path, \FNP_CONSTANT), $this->extConstants, \STRICT_COMPARISON)) {
                                $extra->isExt = true;
                            }
                        break;

                    default: // case class
                            if (in_array(makeFullNsPath($path), array_merge($this->extClasses,
                                                                            $this->extTraits,
                                                                            $this->extEnums,
                                                                            $this->extInterfaces,
                                                                            ), \STRICT_COMPARISON)) {
                                $extra->isExt = true;
                            }
                    }
                }
                break;

            case 'Class' :
                if (isset($extras['ATTRIBUTE'])) {
                    foreach($extras['ATTRIBUTE'] as $extra) {
                        if (in_array($extra->fullnspath ?? self::NOT_PROVIDED, $this->extClasses, \STRICT_COMPARISON)) {
                            $extra->isExt = true;
                        }
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
                foreach($extras['EXTENDS'] as $extra) {
                    if (in_array($extras['EXTENDS']->fullnspath ?? self::NOT_PROVIDED, $this->extInterfaces, \STRICT_COMPARISON)) {
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
                if (in_array(makeFullNsPath($extras['CLASS']->fullnspath ?? self::NOT_PROVIDED), array_merge($this->extClasses,
                                                                                                             $this->extTraits,
                                                                                                             $this->extEnums,
                                                                                                             $this->extInterfaces,
                                                                                                             ), \STRICT_COMPARISON)) {
                    $extras['CLASS']->isExt = true;
                }
                break;

            case 'Parameter' :
                foreach($extras as $extra) {
                    if (in_array(makeFullNsPath($extra->fullnspath ?? self::NOT_PROVIDED), array_merge($this->extClasses,
                                                                                                       $this->extTraits,
                                                                                                       $this->extEnums,
                                                                                                       $this->extInterfaces,
                                                                                                       ), \STRICT_COMPARISON)) {
                        $extra->isExt = true;
                        $atom->isExt = true;
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


            case 'Newcallname' :
                if (in_array($atom->fullnspath, $this->extClasses, \STRICT_COMPARISON)) {
                    $atom->isPhp = true;
                }
                break;

            case 'Newcall' :
                if (empty($path)) {
                    break;
                }
                // Warning : atom->fullnspath for classes (no fallback)
                if (in_array(makeFullNsPath($atom->fullnspath), $this->extClasses, \STRICT_COMPARISON)) {
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
                    $path = $extra->fullnspath ?? self::NOT_PROVIDED;

                    if (in_array(makeFullNsPath($path), array_merge($this->extClasses,
                                                                    $this->extTraits,
                                                                    $this->extEnums,
                                                                    $this->extInterfaces,
                                                                    ), \STRICT_COMPARISON)) {
                        $extra->isExt = true;
                    }
                }
                break;

            case 'Isset' :
            case 'Empty' :
            case 'Unset' :
            case 'Exit'  :
            case 'Echo'  :
            case 'Print' :
                $atom->isExt = false;
                break;

            default :
                // Nothing
        }
    }

    private function checkTypes(array $extras): void {
        foreach($extras as $extra) {
            $id   = strrpos($extra->fullnspath ?? self::NOT_PROVIDED, '\\') ?: 0;
            $path = substr($extra->fullnspath ?? self::NOT_PROVIDED, $id);

            if (in_array(makeFullNsPath($path), array_merge($this->extClasses,
                                                            $this->extTraits,
                                                            $this->extEnums,
                                                            $this->extInterfaces,
                                                            ), \STRICT_COMPARISON)) {
                $extra->isExt = true;
            }
        }
    }
}

?>
