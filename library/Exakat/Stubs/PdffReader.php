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

class PdffReader extends Stubs implements StubsInterface {
    private string $pdffFile    = '';
    private object $data        ;

    // Number of position to extend any configuration when we find a variadic parameter
    private const VARIADIC_SIZE = 10;

    public function __construct(string $pdffFile) {
        $this->pdffFile = $pdffFile;

        $this->data = json_decode(file_get_contents($pdffFile)) ?? new Stdclass();
        assert(json_last_error() == 0, 'Could not decode ' . $pdffFile . '. ' . json_last_error_msg());
    }

    public function getFile(): array {
        return array( basename($this->pdffFile) );
    }

    public function getFunctionList(): array {
        $return = array(array());

        foreach ($this->data->versions as $namespaces => $namespaceDefinitions) {
            foreach ($namespaceDefinitions as $namespace => $definitions) {
                $functions = array_keys((array) ($definitions->functions ?? array()));
                $functions = array_map(function (string $f) use ($namespace): string {
                    return $namespace . $f;
                }, $functions);
                $return[] = $functions;
            }
        }

        return array_merge(...$return);
    }

    public function getFunctionNamesList(): array {
        $return = array(array());

        foreach ($this->data->versions as $namespaces => $namespaceDefinitions) {
            foreach ($namespaceDefinitions as $namespace => $definitions) {
                $functions = array_column((array) ($definitions->functions ?? array()), 'name');
                $return[] = $functions;
            }
        }

        return array_merge(...$return);
    }


    public function getConstantList(): array {
        $return = array(array());

        foreach ($this->data->versions as $namespaces => $namespaceDefinitions) {
            foreach ($namespaceDefinitions as $namespace => $definitions) {
                $constants = array_keys((array) ($definitions->constants ?? array()));
                $constants = array_map(function (string $c) use ($namespace): string {
                    return $namespace . $c;
                }, $constants);
                $return[] = $constants;
            }
        }

        return array_merge(...$return);
    }

    public function getFunctionsArgsInterval(): array {
        $return = array(array());

        foreach ($this->data->versions as $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $functions = (array) ($namespaceDetails->functions ?? array());
                $f = array();
                foreach ($functions as $name => $F) {
                    $f[] = array('name' => $namespace . $name,
                                 'args_min' => ($F->totalParameters ?? 0) - ($F->optionalParameters ?? 0),
                                 'args_max' => ($F->totalParameters ?? 0) + (($F->variadic ?? false) ? 100 : 0 ),
                                 );
                }
                $return[] = $f;
            }
        }

        return array_merge(...$return);
    }

    public function getConstructorsArgsInterval(): array {
        $return = array(array());

        foreach ($this->data->versions as $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $classes = (array) ($namespaceDetails->classes ?? array());
                $f = array();
                foreach ($classes as $name => $C) {
                    if (!isset($C->methods->__construct)) {
                        continue;
                    }

                    $f[] = array('name' => $namespace . $name . '::__construct',
                                 'args_min' => $C->methods->__construct->totalParameters ?? 0,
                                 'args_max' => ($C->methods->__construct->totalParameters ?? 0) + ($C->methods->__construct->optionalParameters ?? 0),
                                 );
                }
                $return[] = $f;
            }
        }

        return array_merge(...$return);
    }

    public function getMethodsArgsInterval(): array {
        $return = array(array());

        foreach ($this->data->versions as $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $classes = (array) ($namespaceDetails->classes ?? array());
                foreach ($classes as $name => $C) {
                    if (!isset($C->methods)) {
                        continue;
                    }
                    foreach ($C->methods as $mname => $M) {
                        $f = array();
                        $f[] = array('name'     => $namespace . $name . '::' . $mname,
                                     'args_min' => $M->totalParameters ?? 0,
                                     'args_max' => ($M->totalParameters ?? 0) + ($M->optionalParameters ?? 0),
                                 );
                        $return[] = $f;
                    }
                }

                $traits = (array) ($namespaceDetails->traits ?? array());
                foreach ($traits as $name => $T) {
                    if (!isset($T->methods)) {
                        continue;
                    }
                    foreach ($T->methods as $mname => $M) {
                        $f = array();
                        $f[] = array('name'     => $namespace . $name . '::' . $mname,
                                     'args_min' => $M->totalParameters ?? 0,
                                     'args_max' => ($M->totalParameters ?? 0) + ($M->optionalParameters ?? 0),
                                 );
                        $return[] = $f;
                    }
                }
            }
        }

        return array_merge(...$return);
    }

    public function getInterfaceList(): array {
        $return = array(array());

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $interfaces = array_keys((array) ($namespaceDetails->interfaces ?? array()));
                $interfaces = array_map(function (string $i) use ($namespace): string {
                    return $namespace . $i;
                }, $interfaces);
                $return[] = $interfaces;
            }
        }

        return array_merge(...$return);
    }

    public function getEnumList(): array {
        $return = array(array());

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $enums = array_keys((array) ($namespaceDetails->enums ?? array()));
                $enums = array_map(function (string $e) use ($namespace): string {
                    return $namespace . $e;
                }, $enums);
                $return[] = $enums;
            }
        }

        return array_merge(...$return);
    }

    public function getTraitList(): array {
        $return = array(array());

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $traits = array_keys((array) ($namespaceDetails->traits ?? array()));
                $traits = array_map(function (string $t) use ($namespace): string {
                    return $namespace . $t;
                }, $traits);
                $return[] = $traits;
            }
        }

        return array_merge(...$return);
    }

    public function getNamespaceList(): array {
    	$first = array_keys((array) $this->data->versions)[0];
    	$return = array_keys((array) $this->data->versions->$first);
    	$return = array_map('rtrim', $return, array_pad(array(), count($return), '\\'));
        return array_filter($return);
    }

    public function getClassList(): array {
        $return = array(array());

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $classes = array_keys((array) ($namespaceDetails->classes ?? array()));
                $classes = array_map(function (string $c) use ($namespace): string {
                    return $namespace . $c;
                }, $classes);
                $return[] = $classes;
            }
        }

        return array_merge(...$return);
    }

    public function getClassConstantList(): array {
        $return = array(array());

        // @todo : how to handle when the constant is defined in a class/interface above
        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $classConstants = array_keys((array) ($class->constants ?? array()));
                    $classConstants = array_map(function (string $constant) use ($namespace, $class): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $constant;
                    }, $classConstants);
                    $return[] = $classConstants;
                }

                foreach ($namespaceDetails->interfaces as $interface) {
                    $classConstants = array_keys((array) ($interface->constants ?? array()));
                    $classConstants = array_map(function (string $constant) use ($namespace, $interface): string {
                        return mb_strtolower($namespace . $interface->name) . '::' . $constant;
                    }, $classConstants);
                    $return[] = $classConstants;
                }

                foreach ($namespaceDetails->enums as $enum) {
                    $classConstants = array_keys((array) ($enum->constants ?? array()));
                    $classConstants = array_map(function (string $constant) use ($namespace, $enum): string {
                        return mb_strtolower($namespace . $enum->name) . '::' . $constant;
                    }, $classConstants);
                    $return[] = $classConstants;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getEnumCasesList(): array {
        $return = array(array());

        // @todo : how to handle when the constant is defined in a class/interface above
        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->enums as $enum) {
                    $cases = array_keys((array) ($enum->cases ?? array()));
                    $cases = array_map(function (string $case) use ($namespace, $enum): string {
                        return mb_strtolower($namespace . $enum->name) . '::' . $case;
                    }, $cases);
                    $return[] = $cases;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getClassPropertyList(): array {
        $return = array(array());

        $filter = function (Stdclass $property): bool {
            return $property->static === false;
        };
        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $properties = array_keys(array_filter((array) $class->properties ?? array(), $filter));
                    $properties = array_map(function (string $property) use ($namespace, $class): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $property;
                    }, $properties);
                    $return[] = $properties;
                }

                foreach ($namespaceDetails->traits as $trait) {
                    $properties = array_keys( array_filter((array) $trait->properties ?? array(), $filter));
                    $properties = array_map(function (string $property) use ($namespace, $trait): string {
                        return mb_strtolower($namespace . $trait->name) . '::' . $property;
                    }, $properties);
                    $return[] = $properties;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getClassStaticPropertyList(): array {
        $return = array(array());

        $filter = function (Stdclass $property): bool {
            return $property->static === true;
        };
        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $properties = array_keys(array_filter((array) $class->properties ?? array(), $filter));
                    $properties = array_map(function (string $property) use ($namespace, $class): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $property;
                    }, $properties);
                    $return[] = $properties;
                }

                foreach ($namespaceDetails->traits as $trait) {
                    $properties = array_keys(array_filter((array) $trait->properties ?? array(), $filter));
                    $properties = array_map(function (string $property) use ($namespace, $trait): string {
                        return mb_strtolower($namespace . $trait->name) . '::' . $property;
                    }, $properties);
                    $return[] = $properties;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getClassMethodList(): array {
        $return = array(array());

        $filter = function (Stdclass $property): bool {
            return $property->static === false;
        };
        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $methods = array_keys((array_filter((array) ($class->methods ?? array()), $filter)));
                    $methods = array_map(function (string $method) use ($namespace, $class): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $method;
                    }, $methods);
                    $return[] = $methods;
                }

                foreach ($namespaceDetails->traits as $trait) {
                    $methods = array_keys(array_filter((array) $trait->methods ?? array(), $filter));
                    $methods = array_map(function (string $method) use ($namespace, $trait): string {
                        return mb_strtolower($namespace . $trait->name) . '::' . $method;
                    }, $methods);
                    $return[] = $methods;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getClassMethodNamesList(): array {
        $return = array(array());

        $filter = function (Stdclass $property): bool {
            return $property->static === false;
        };
        foreach ($this->data->versions as $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $methods = array_column((array_filter((array) ($class->methods ?? array()), $filter)), 'name');
                    $methods = array_map(function (string $method) use ($namespace, $class): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $method;
                    }, $methods);
                    $return[] = $methods;
                }

                foreach ($namespaceDetails->traits as $trait) {
                    $methods = array_column(array_filter((array) $trait->methods ?? array(), $filter), 'name');
                    $methods = array_map(function (string $method) use ($namespace, $trait): string {
                        return mb_strtolower($namespace . $trait->name) . '::' . $method;
                    }, $methods);
                    $return[] = $methods;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getClassStaticMethodList(): array {
        $return = array(array());

        $filter = function (Stdclass $property): bool {
            return $property->static === true;
        };
        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $methods = array_keys(array_filter((array) ($class->methods ?? array()), $filter));
                    $methods = array_map(function (string $method) use ($namespace, $class): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $method;
                    }, $methods);
                    $return[] = $methods;
                }

                foreach ($namespaceDetails->traits as $trait) {
                    $methods = array_keys(array_filter((array) $trait->methods ?? array(), $filter));
                    $methods = array_map(function (string $method) use ($namespace, $trait): string {
                        return mb_strtolower($namespace . $trait->name) . '::' . $method;
                    }, $methods);
                    $return[] = $methods;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getPropertyList(): array {
        $return = array(array());

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class => $body) {
                    $classProperties = array_keys((array) ($body->properties ?? array()));
                    if (empty($classProperties)) {
                        continue;
                    }
                    $list = $body->properties;
                    $classProperties = array_filter($classProperties, function (string $property) use ($list): bool {
                        return $list->{$property}->static === false;
                    });
                    $classProperties = array_map(function (string $property) use ($namespace, $class): string {
                        return $namespace . $class . '::' . $property;
                    }, $classProperties);
                    $return[] = $classProperties;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getMethodList(): array {
        $return = array(array());

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class => $body) {
                    $classMethods = array_keys((array) ($body->methods ?? array()));
                    if (empty($classMethods)) {
                        continue;
                    }
                    $list = $body->methods;
                    $classMethods = array_filter($classMethods, function (string $method) use ($list): bool {
                        return $list->{$method}->static === false;
                    });
                    $classMethods = array_map(function (string $method) use ($namespace, $class): string {
                        return $namespace . $class . '::' . $method;
                    }, $classMethods);
                    $return[] = $classMethods;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getInterfaceMethodsNameAndCount(): array {
        $return = array();

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->interfaces as $interface => $body) {
                    foreach ($body->methods as $name => $details) {
                        $return[mb_strtolower($namespace . $interface)][] = (object) array('name'  => $name,
                                                                                           'count' => count($details->parameters),
                            );
                    }
                }
            }
        }

        return $return;
    }

    public function getFinalClasses(): array {
        $return = array();

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $classes = array_filter((array) $namespaceDetails->classes ?? array(), function (object $class) {
                    return $class->final ?? false;
                });
                $classes = array_keys($classes);
                $classes = array_map(function (string $c) use ($namespace): string {
                    return $namespace . $c;
                }, $classes);
                $return[] = $classes;
            }
        }

        return array_merge(...$return);
    }

    public function getFinalClassMethods(): array {
        $return = array(array());

        // @todo : how to handle when the method is defined in a class/interface above ??
        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $methods = (array) ($class->methods ?? array());
                    $methods = array_filter($methods, function (object $method) {
                        return $method->final ?? false;
                    });
                    $methods = array_keys($methods);
                    $methods = array_map(function (string $method) use ($namespace, $class): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $method;
                    }, $methods);
                    $return[] = $methods;
                }

                foreach ($namespaceDetails->interfaces as $interface) {
                    $methods = (array) ($interface->methods ?? array());
                    $methods = array_filter($methods, function (object $method) {
                        return $method->final ?? false;
                    });
                    $methods = array_keys($methods);
                    $methods = array_map(function (string $method) use ($namespace, $interface): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $method;
                    }, $methods);
                    $return[] = $methods;
                }

                foreach ($namespaceDetails->enums as $enum) {
                    $methods = (array) ($enum->methods ?? array());
                    $methods = array_filter($methods, function (object $method) {
                        return $method->final ?? false;
                    });
                    $methods = array_keys($methods);
                    $methods = array_map(function (string $method) use ($namespace, $enum): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $method;
                    }, $methods);
                    $return[] = $methods;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getFinalClassConstants(): array {
        $return = array(array());

        // @todo : how to handle when the constant is defined in a class/interface above
        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $classConstants = (array) ($class->constants ?? array());
                    $classConstants = array_filter($classConstants, function (object $constant) {
                        return $constant->final ?? false;
                    });
                    $classConstants = array_keys($classConstants);
                    $classConstants = array_map(function (string $constant) use ($namespace, $class): string {
                        return mb_strtolower($namespace . $class->name) . '::' . $constant;
                    }, $classConstants);
                    $return[] = $classConstants;
                }

                foreach ($namespaceDetails->interfaces as $interface) {
                    $classConstants = (array) ($interface->constants ?? array());
                    $classConstants = array_filter($classConstants, function (object $constant) {
                        return $constant->final ?? false;
                    });
                    $classConstants = array_keys($classConstants);
                    $classConstants = array_map(function (string $constant) use ($namespace, $interface): string {
                        return mb_strtolower($namespace . $interface->name) . '::' . $constant;
                    }, $classConstants);
                    $return[] = $classConstants;
                }

                foreach ($namespaceDetails->enums as $enum) {
                    $classConstants = (array) ($enum->constants ?? array());
                    $classConstants = array_filter($classConstants, function (object $constant) {
                        return $constant->final ?? false;
                    });
                    $classConstants = array_keys($classConstants);
                    $classConstants = array_map(function (string $constant) use ($namespace, $enum): string {
                        return mb_strtolower($namespace . $enum->name) . '::' . $constant;
                    }, $classConstants);
                    $return[] = $classConstants;
                }
            }
        }

        return array_merge(...$return);
    }

    public function getClassImplementingList(): array {
        $return = array();

        foreach ($this->data->versions as $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $classes = (array) ($namespaceDetails->classes ?? array());
                $f = array();
                foreach ($classes as $name => $C) {
                    if (!empty($C->implements)) {
                        $return[mb_strtolower($namespace . $name)] = array_column($C->implements, 'target');
                    }
                }
            }
        }

        return $return;
    }

    public function getFunctionsReferenceArgs(): array {
        $return = array();
        /*
            array of array (function =>, position of the referenced argument)
        */

        foreach ($this->data->versions as $namespaces => $namespaceDefinitions) {
            foreach ($namespaceDefinitions as $namespace => $definitions) {
                foreach ($definitions->functions ?? array() as $function => $details) {
                    foreach ($details->parameters as $position => $parameter) {
                        if ($parameter->reference === true) {
                            $return[] = array('function' => $namespace . $details->name,
                                              'position' => $position
                                             );
                            // With a variadic, the last arguments are also references
                            if ($parameter->variadic ?? false === true) {
                                for ($i = 0; $i < self::VARIADIC_SIZE; ++$i) {
                                    $return[] = array('function' => $namespace . $details->name,
                                                      'position' => $position + $i
                                                     );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $return;
    }

    public function getPropertyListWithVisibility(string $visibility): array {
        $return = array();

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $id = mb_strtolower($namespace . $class->name);
                    foreach ($class->properties as $property) {
                        if ($property->visibility === $visibility) {
                            array_collect_by($return, $id, $property->name);
                        }
                    }
                }

                foreach ($namespaceDetails->traits as $trait) {
                    $id = mb_strtolower($namespace . $trait->name);
                    foreach ($trait->properties as $property) {
                        if ($property->visibility === $visibility) {
                            array_collect_by($return, $id, $property->name);
                        }
                    }
                }
            }
        }

        return $return;
    }


    public function getMethodListWithVisibility(string $visibility): array {
        $return = array();

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $id = mb_strtolower($namespace . $class->name);
                    foreach ($class->methods ?? array() as $method) {
                        if (($method->visibility ?? 'public' )=== $visibility) {
                            array_collect_by($return, $id, $method->name);
                        }
                    }
                }

                foreach ($namespaceDetails->traits as $trait) {
                    $id = mb_strtolower($namespace . $trait->name);
                    foreach ($trait->methods as $method) {
                        if (($method->visibility ?? 'public') === $visibility) {
                            array_collect_by($return, $id, $method->name);
                        }
                    }
                }
            }
        }

        return $return;
    }

    public function getConstantListWithVisibility(string $visibility): array {
        $return = array();

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                foreach ($namespaceDetails->classes as $class) {
                    $id = mb_strtolower($namespace . $class->name);
                    foreach ($class->constants as $const) {
                        if ($const->visibility === $visibility) {
                            array_collect_by($return, $id, $const->name);
                        }
                    }
                }

                foreach ($namespaceDetails->interfaces as $interface) {
                    $id = mb_strtolower($namespace . $interface->name);
                    foreach ($interface->constants as $const) {
                        if ($const->visibility === $visibility) {
                            array_collect_by($return, $id, $const->name);
                        }
                    }
                }
            }
        }

        return $return;
    }

    public function getFunctionParameterNames(array $functions): array {
        $return = array();

        foreach ($this->data->versions as $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $functionsList = (array) ($namespaceDetails->functions ?? array());
                foreach ($functionsList as $name => $C) {
                    if (!isset($C->parameters)) {
                        continue;
                    }

                    if (in_array(mb_strtolower($namespace . $name), $functions, \STRICT_COMPARISON) === false) {
                        continue;
                    }

                    $return[mb_strtolower($namespace . $name)] = array_column($C->parameters, 'name');
                }
            }
        }

        return $return;
    }

    public function getNoNullReturningFunctions(): array {
        $return = array();

        foreach ($this->data->versions as $namespace => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $functions = (array) ($namespaceDetails->functions ?? array());
                foreach ($functions as $name => $C) {
                    if (!isset($C->returntypehints)) {
                        continue;
                    }
                    $types = array_column($C->returntypehints, 'typehint');

                    if (in_array('\\null', $types, \STRICT_COMPARISON)) {
                        continue;
                    }

                    $return[] = mb_strtolower($namespace . $name);
                }
            }
        }

        return $return;
    }

    public function getVoidReturningFunctions(): array {
        $return = array();

        foreach ($this->data->versions as $namespace => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $functions = (array) ($namespaceDetails->functions ?? array());
                foreach ($functions as $name => $C) {
                    if (!isset($C->returntypehints)) {
                        continue;
                    }
                    $types = array_column($C->returntypehints, 'typehint');

                    if (array('\\void') === array_values($types)) {
                        $return[] = mb_strtolower($namespace . $name);
                    }
                }
            }
        }

        return $return;
    }

    public function getNativeMethodReturn(): array {
        $return = array();

        foreach ($this->data->versions as $namespace => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $classes = (array) ($namespaceDetails->classes ?? array());
                foreach ($classes as $name => $class) {
                    if (empty($class->methods)) {
                        continue;
                    }
                    $methods = (array) ($class->methods ?? array());
                    foreach ($methods as $method) {
                        $types = array_column($method->returntypehints, 'typehint');

                        $n = mb_strtolower($namespace . $name);
                        if (isset($return[$n])) {
                            $return[mb_strtolower($namespace . $name)][$method->name] = $types;
                        } else {
                            $return[mb_strtolower($namespace . $name)] = array($method->name => $types);
                        }
                    }
                }
            }
        }

        return $return;
    }

    public function getAbstractClassList(): array {
        $return = array(array());

        foreach ($this->data->versions as $version => $namespaceList) {
            foreach ($namespaceList as $namespace => $namespaceDetails) {
                $classes = (array) ($namespaceDetails->classes ?? array());
                $classes = array_filter($classes, function ($class): bool {
                    return $class->abstract ?? false;
                });
                $classes = array_map(function (object $class) use ($namespace): string {
                    return mb_strtolower($namespace . $class->name);
                }, $classes);
                $return[] = $classes;
            }
        }

        return array_merge(...$return);
    }
}

?>
