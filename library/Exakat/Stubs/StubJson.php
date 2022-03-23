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

namespace Exakat\Stubs;

use Stdclass;

class StubJson extends Stubs implements StubsInterface {
    private $stubFile    = '';
    private $stub        = array();

    public function __construct(string $stubFile) {
        $this->stubFile = $stubFile;

        $this->stub = json_decode(file_get_contents($stubFile)) ?? new Stdclass();
    }

    public function getFile(): array {
        return array( basename($this->stubFile) );
    }

    public function getFunctionList(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            $functions = array_keys((array) ($definitions->functions ?? array()));
            $functions = array_map(function (string $f) use ($namespace): string { return $namespace . $f;}, $functions);
            $return[] = $functions;
        }

        return array_merge(...$return);
    }

    public function getConstantList(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            $constants = array_keys((array) ($definitions->constants ?? array()));
            $constants = array_map(function (string $c) use ($namespace): string { return $namespace . $c;}, $constants);
            $return[] = $constants;
        }

        return array_merge(...$return);
    }

    public function getFunctionsArgsInterval(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            $functions = (array) ($definitions->functions ?? array());
            $f = array();
            foreach($functions as $name => $F) {
                $f[] = array('name' => $namespace . $name,
                             'args_min' => count((array) ($F->arguments ?? array())),
                             'args_max' => count((array) ($F->arguments ?? array())),
                             );
            }
            $return[] = $f;
        }
        return array_merge(...$return);
    }

    public function getInterfaceList(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            $interfaces = array_keys((array) ($definitions->interfaces ?? array()));
            $interfaces = array_map(function (string $c) use ($namespace): string { return $namespace . $c;}, $interfaces);
            $return[] = $interfaces;
        }

        return array_merge(...$return);
    }

    public function getTraitList(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            $traits = array_keys((array) ($definitions->traits ?? array()));
            $traits = array_map(function (string $c) use ($namespace): string { return $namespace . $c;}, $traits);
            $return[] = $traits;
        }

        return array_merge(...$return);
    }

    public function getClassList(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            $classes = array_keys((array) ($definitions->classes ?? array()));
            $classes = array_map(function (string $c) use ($namespace): string { return $namespace . $c;}, $classes);
            $return[] = $classes;
        }

        return array_merge(...$return);
    }

    public function getClassConstantList(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            foreach((array) ($definitions->classes ?? array()) as $class => $body) {
                $classConstants = array_keys((array) ($body->constants ?? array()));
                if (empty($classConstants)) {
                    continue;
                }
                $classConstants = array_map(function (string $constant) use ($namespace, $class): string { return $namespace . $class . '::' . $constant;}, $classConstants);
                $return[] = $classConstants;
            }
        }

        return array_merge(...$return);
    }

    public function getClassPropertyList(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            foreach((array) ($definitions->classes ?? array()) as $class => $body) {
                $classProperties = array_keys((array) ($body->properties ?? array()));
                if (empty($classProperties)) {
                    continue;
                }
                $list = $body->properties;
                $classProperties = array_filter($classProperties, function (string $property) use ($list): bool { return $list->{$property}->static === true; });
                $classProperties = array_map(function (string $property) use ($namespace, $class): string { return $namespace . $class . '::' . $property;}, $classProperties);
                $return[] = $classProperties;
            }
        }

        return array_merge(...$return);
    }

    public function getClassMethodList(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            foreach((array) ($definitions->classes ?? array()) as $class => $body) {
                $classMethods = array_keys((array) ($body->methods ?? array()));
                if (empty($classMethods)) {
                    continue;
                }
                $list = $body->methods;
                $classMethods = array_filter($classMethods, function (string $method) use ($list): bool { return $list->{$method}->static === true; });
                $classMethods = array_map(function (string $method) use ($namespace, $class): string { return $namespace . $class . '::' . $method;}, $classMethods);
                $return[] = $classMethods;
            }
        }

        return array_merge(...$return);
    }


    public function getPropertyList(): array {
        $return = array(array());

        foreach($this->stub->versions as $namespace => $definitions) {
            foreach((array) ($definitions->classes ?? array()) as $class => $body) {
                $classProperties = array_keys((array) ($body->properties ?? array()));
                if (empty($classProperties)) {
                    continue;
                }
                $list = $body->properties;
                $classProperties = array_filter($classProperties, function (string $property) use ($list): bool { return $list->{$property}->static === false; });
                $classProperties = array_map(function (string $property) use ($namespace, $class): string { return $namespace . $class . '::' . $property;}, $classProperties);
                $return[] = $classProperties;
            }
        }

        return array_merge(...$return);
    }

    public function getEnumCasesList(): array {
        return array();
    }

    public function getClassStaticPropertyList(): array {
        return array();
    }

    public function getEnumList(): array {
        return array();
    }

    public function getClassStaticMethodList(): array {
        return array();
    }

    public function getInterfaceMethodsNameAndCount(): array {
        return array();
    }

    public function getMethodList(): array {
        return array();
    }

    public function getFinalClasses(): array {
        return array();
    }

    public function getFinalClassConstants(): array {
        return array();
    }

    public function getFunctionNamesList(): array {
        return array();
    }

    public function getClassMethodNamesList(): array {
        return array();
    }

    public function getNamespaceList(): array {
        return array();
    }

    public function getConstructorsArgsInterval(): array {
        return array();
    }

    public function getMethodsArgsInterval(): array {
        return array();
    }
}

?>
