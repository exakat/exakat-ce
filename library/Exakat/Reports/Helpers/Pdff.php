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

namespace Exakat\Reports\Helpers;

class Pdff {
    private array  $versions = array();
    private string $current;

    public function setVersion(string $s = '*') {
        $this->current = $s;
        if (!isset($this->versions[$this->current])) {
            $this->versions[$s] = new PdffVersion();
        }

        return $this->versions[$s];
    }

    public function getVersion(string $s = '*') {
        return $this->versions[$s];
    }

    public function toArray(): array {
        $array = array('versions' => $this->versions);

        foreach ($array['versions'] as &$version) {
            $version = $version->toArray();
        }
        unset($version);

        return $array;
    }
}

interface PdffHasAttribute {
    public function addAttribute(PdffAttribute $attribute): void;
}

interface PdffHasProperty {
    public function addProperty(string $name, PdffProperty $property): void;
}

interface PdffHasMethod {
    public function addMethod(string $name, PdffMethod $method): void;
}

interface PdffHasTypehint {
    public function addTypehint(PdffTypehint $typehint): void;
}

interface PdffHasConstant {
    public function addClassConstant(string $name, PdffClassConstant $classConstant): void;
}

interface PdffHasPhpdoc {
    public function addPhpdoc(PdffPhpdoc $phpdoc): void;
}

interface PdffHasParameter {
    public function addParameter(int $rank, PdffParameter $parameter);
}

class PdffVersion {
    private array  $namespaces = array();
    private string $current = '*';

    public function setNamespace(string $s = '*'): PdffNamespace {
        $this->current = mb_strtolower($s);

        if (!isset($this->namespaces[$this->current])) {
            $this->namespaces[$this->current] = new PdffNamespace($s);
        }

        return $this->namespaces[$this->current];
    }

    public function getNamespace(): PdffNamespace {
        return $this->namespaces[$this->current];
    }

    public function toArray(): array {
        $array = $this->namespaces;

        foreach ($array as &$namespace) {
            $namespace = $namespace->toArray();
        }
        unset($namespace);

        return $array;
    }
}

class PdffNamespace {
    private $name       = '';
    private $constants  = array();
    private $functions  = array();
    private $traits     = array();
    private $classes    = array();
    private $interfaces = array();
    private $enums      = array();

    public function __construct(string $name) {
        $this->name       = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function addConstant(string $name, PdffConstant $constant) {
        $this->constants[$name] = $constant;
    }

    public function addFunction(string $name, PdffFunction $function) {
        $this->functions[mb_strtolower($name)] = $function;
    }

    public function addClass(string $name, PdffClass $class) {
        $this->classes[mb_strtolower($name)] = $class;
    }

    public function addInterface(string $name, PdffInterface $interface) {
        $this->interfaces[mb_strtolower($name)] = $interface;
    }

    public function addTrait(string $name, PdffTrait $trait) {
        $this->traits[mb_strtolower($name)] = $trait;
    }

    public function addEnum(string $name, PdffEnum $enum) {
        $this->enums[mb_strtolower($name)] = $enum;
    }

    public function toArray(): array {
        $array = array(
            'name'       => $this->name,
        );

        foreach (array('constants', 'functions', 'traits', 'classes', 'interfaces', 'enums') as $type) {
            $array[$type] = array();

            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffConstant implements PdffHasPhpdoc {
    private $name        = '';
    private $value       = '';
    private $expression  = false;
    private $phpdoc      = array();

    public function __construct(string $name, string $value, bool $expression) {
        $this->name       = $name;
        $this->value      = $value;
        $this->expression = $expression;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function toArray(): array {
        $array = array(
            'name'       => $this->name,
            'phpdoc'     => array(),
            'expression' => $this->expression,
            'value'      => $this->value,
        );

        foreach (array('phpdoc') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffClassConstant implements PdffHasAttribute, PdffHasPhpdoc {
    private $name        = '';
    private $value       = '';
    private $expression  = false;
    private $visibility  = 'none';
    private $final       = false;

    private $attributes  = array();
    private $phpdoc      = array();

    public function __construct(string $name, string $value, bool $expression, string $visibility, bool $final) {
        $this->name       = $name;
        $this->value      = $value;
        $this->expression = $expression;
        $this->final      = $final;
        $this->visibility = $visibility;
    }

    public function addAttribute(PdffAttribute $attribute): void {
        $this->attributes[] = $attribute;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function toArray(): array {
        $array = array(
            'name'       => $this->name,
            'value'      => $this->value,
            'expression' => $this->expression,
            'final'      => $this->final,
            'visibility' => $this->visibility,
            'phpdoc'     => array(),
            'attributes' => array(),
        );

        foreach (array('phpdoc', 'attributes') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffClassCase implements PdffHasPhpdoc {
    private $name        = '';
    private $value       = '';
    private $phpdoc      = array();

    public function __construct(string $name, string $value) {
        $this->name       = $name;
        $this->value      = $value;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function toArray(): array {
        $array = array(
            'name'       => $this->name,
            'value'      => $this->value,
            'phpdoc'     => array(),
        );

        foreach (array('phpdoc') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffFunction implements PdffHasAttribute, PdffHasTypehint, PdffHasPhpdoc, PdffHasParameter {
    private $name               = '';
    private $returntype         = '';
    private $reference          = false;
    private $totalParameters    = 0;
    private $optionalParameters = 0;
    private $variadic           = false;

    private $returntypehints    = array();
    private $parameters         = array();
    private $attributes         = array();
    private $phpdoc             = array();

    public function __construct(string $name, string $returntype, bool $reference) {
        $this->name       = $name;
        $this->returntype = $returntype;
        $this->reference  = $reference;
    }

    public function addAttribute(PdffAttribute $attribute): void {
        $this->attributes[] = $attribute;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function addParameter(int $rank, PdffParameter $parameter) {
        $this->parameters[$rank] = $parameter;

        ++$this->totalParameters;
        if ($parameter->hasDefault() || $this->optionalParameters > 0) {
            ++$this->optionalParameters;
        }

        $this->variadic = $parameter->isVariadic();
    }

    public function addTypehint(PdffTypehint $typehint): void {
        $this->returntypehints[] = $typehint;
    }

    public function toArray(): array {
        $array = array(
            'name'               => $this->name,
            'returntype'         => $this->returntype,
            'reference'          => $this->reference,
            'returntypehints'    => array(),
            'parameters'         => array(),
            'totalParameters'    => $this->totalParameters,
            'optionalParameters' => $this->optionalParameters,
            'variadic'           => $this->variadic,
            'attributes'         => array(),
            'phpdoc'             => array(),
        );

        foreach (array('attributes', 'parameters', 'returntypehints', 'phpdoc') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffAttribute {
    private $attribute        = '';

    public function __construct(string $attribute) {
        $this->attribute       = $attribute;
    }

    public function toArray(): array {
        $array = array(
            'attribute'          => $this->attribute,
        );

        return $array;
    }
}

class PdffPhpdoc {
    private $phpdoc        = '';

    public function __construct(string $phpdoc) {
        $this->phpdoc       = $phpdoc;
    }

    public function toArray(): array {
        $array = array(
            'phpdoc'          => $this->phpdoc,
        );

        return $array;
    }
}

class PdffTypehint {
    private $typehint        = '';

    public function __construct(string $typehint) {
        if (in_array($typehint, array('false', 'string', 'int', 'null', 'array', 'float', 'object', 'bool', 'mixed', 'true'), true)) {
            $typehint = '\\' . $typehint;
        }
        $this->typehint       = $typehint;
    }

    public function toArray(): array {
        $array = array(
            'typehint'          => $this->typehint,
        );

        return $array;
    }
}

class PdffExtends {
    private $target        = '';

    public function __construct(string $target) {
        $this->target     = $target;
    }

    public function toArray(): array {
        $array = array(
            'target'        => $this->target,
        );

        return $array;
    }
}

class PdffParameter implements PdffHasAttribute, PdffHasTypehint, PdffHasPhpdoc {
    private $name         = '';
    private $rank         = 0;
    private $default      = '';
    private $variadic     = false;
    private $expression   = false;
    private $reference    = false;
    private $hasDefault   = false;
    private $typehinttype = '';

    private $typehints    = array();
    private $attributes   = array();
    private $phpdoc       = array();

    public function __construct(string $name, int $rank, bool $variadic, bool $reference, bool $hasDefault, string $default, bool $expression, string $typehinttype) {
        $this->name         = $name;
        $this->rank         = $rank;
        $this->variadic     = $variadic;
        $this->reference    = $reference;
        $this->default      = $default;
        $this->hasDefault   = $hasDefault;
        $this->expression   = $expression;
        $this->typehinttype = $typehinttype;
    }

    public function addAttribute(PdffAttribute $attribute): void {
        $this->attributes[] = $attribute;
    }

    public function addTypehint(PdffTypehint $typehint): void {
        $this->typehints[] = $typehint;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function isVariadic(): bool {
        return $this->variadic;
    }

    public function hasDefault(): bool {
        return $this->hasDefault;
    }

    public function toArray(): array {
        $array = array(
            'name'          => $this->name,
            'rank'          => $this->rank,
            'variadic'      => $this->variadic,
            'reference'     => $this->reference,
            'hasDefault'    => $this->hasDefault,
            'default'       => $this->default,
            'expression'    => $this->expression,
            'typehinttype'  => $this->typehinttype,
            'phpdoc'        => array(),
            'typehints'     => array(),
            'attributes'    => array(),
        );

        foreach (array('typehints', 'attributes', 'phpdoc') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffInterface implements PdffHasMethod, PdffHasConstant, PdffHasPhpdoc {
    private $name         = '';
    private $phpdoc       = array();
    private $extends      = array();
    private $constants    = array();
    private $methods      = array();

    public function __construct(string $name) {
        $this->name       = $name;
    }

    public function addClassConstant(string $name, PdffClassConstant $classConstant): void {
        $this->constants[$name] = $classConstant;
    }

    public function addMethod(string $name, PdffMethod $method): void {
        $this->methods[mb_strtolower($name)] = $method;
    }

    public function addExtends(PdffExtends $extend): void {
        $this->extends[] = $extend;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function toArray(): array {
        $array = array(
            'name'         => $this->name,
            'phpdoc'       => array(),
            'extends'      => array(),
            'constants'    => array(),
            'methods'      => array(),
        );

        foreach (array('extends', 'constants', 'methods', 'phpdoc') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }


        return $array;
    }
}

class PdffTrait implements PdffHasMethod, PdffHasProperty, PdffHasPhpdoc {
    private $name         = '';

    private $phpdoc       = array();
    private $uses         = array();
    private $properties   = array();
    private $methods      = array();

    public function __construct(string $name) {
        $this->name       = $name;
    }

    public function addProperty(string $name, PdffProperty $property): void {
        $this->properties[$name] = $property;
    }

    public function addMethod(string $name, PdffMethod $method): void {
        $this->methods[mb_strtolower($name)] = $method;
    }

    public function addUse(PdffExtends $use): void {
        $this->uses[] = $use;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function toArray(): array {
        $array = array(
            'name'         => $this->name,
            'phpdoc'       => array(),
            'uses'         => array(),
            'properties'   => array(),
            'methods'      => array(),
        );

        foreach (array('uses', 'properties', 'methods', 'phpdoc') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffEnum implements PdffHasMethod, PdffHasConstant, PdffHasPhpdoc {
    private $name         = '';
    private $typehint     = '';

    private $phpdoc       = array();
    private $constants    = array();
    private $methods      = array();
    private $cases        = array();

    public function __construct(string $name, string $typehint = '') {
        $this->name       = $name;
        $this->typehint   = $typehint;
    }

    public function addMethod(string $name, PdffMethod $method): void {
        $this->methods[mb_strtolower($name)] = $method;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function addClassConstant(string $name, PdffClassConstant $classConstant): void {
        $this->constants[$name] = $classConstant;
    }

    public function addCase(string $name, PdffClassCase $case): void {
        $this->cases[$name] = $case;
    }

    public function toArray(): array {
        $array = array(
            'name'         => $this->name,
            'typehint'     => $this->typehint,
            'phpdoc'       => array(),
            'constants'    => array(),
            'methods'      => array(),
            'cases'        => array(),
        );

        foreach (array('methods', 'constants', 'cases', 'phpdoc') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffMethod implements PdffHasAttribute, PdffHasTypehint, PdffHasPhpdoc, PdffHasParameter {
    private $name               = '';
    private $final              = false;
    private $static             = false;
    private $reference          = false;
    private $visibility         = '';
    private $returntype         = '';
    private $totalParameters    = 0;
    private $optionalParameters = 0;
    private $variadic           = false;

    private $attributes       = array();
    private $returntypehints  = array();
    private $parameters       = array();
    private $phpdoc           = array();

    public function __construct(string $name, string $visibility, bool $final, bool $static, bool $reference, string $returntype) {
        $this->name        = $name;
        $this->visibility  = $visibility;
        $this->final       = $final;
        $this->static      = $static;
        $this->reference   = $reference;
        $this->returntype  = $returntype;
    }

    public function addAttribute(PdffAttribute $attribute): void {
        $this->attributes[] = $attribute;
    }

    public function addParameter(int $rank, PdffParameter $parameter) {
        $this->parameters[$rank] = $parameter;

        ++$this->totalParameters;
        if ($parameter->hasDefault() || $this->optionalParameters > 0) {
            ++$this->optionalParameters;
        }

        $this->variadic = $parameter->isVariadic();
    }

    public function addTypehint(PdffTypehint $typehint): void {
        $this->returntypehints[] = $typehint;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function toArray(): array {
        $array = array(
            'name'               => $this->name,
            'phpdoc'             => array(),
            'visibility'         => $this->visibility,
            'attributes'         => array(),
            'parameters'         => array(),
            'totalParameters'    => $this->totalParameters,
            'optionalParameters' => $this->optionalParameters,
            'variadic'           => $this->variadic,
            'final'              => $this->final,
            'static'             => $this->static,
            'reference'          => $this->reference,
            'returntype'         => $this->returntype,
            'returntypehints'    => array(),
        );

        foreach (array('returntypehints', 'attributes', 'parameters', 'phpdoc') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffProperty implements PdffHasTypehint, PdffHasPhpdoc, PdffHasAttribute {
    private $name         = '';
    private $var          = false;
    private $static       = false;
    private $readonly     = false;
    private $hasDefault   = false;
    private $expression   = false;
    private $visibility   = '';
    private $typehinttype = '';
    private $init         = '';

    private $attributes   = array();
    private $typehints    = array();
    private $phpdoc       = array();

    public function __construct(string $name, string $visibility, string $init, bool $hasDefault, bool $expression, bool $static, bool $readonly, string $typehinttype, bool $var) {
        $this->name         = $name;
        $this->visibility   = $visibility;
        $this->init         = $init;
        $this->static       = $static;
        $this->readonly     = $readonly;
        $this->hasDefault   = $hasDefault;
        $this->expression   = $expression;
        $this->typehinttype = $typehinttype;
        $this->var          = $var;
    }

    public function addTypehint(PdffTypehint $typehint): void {
        $this->typehints[] = $typehint;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function addAttribute(PdffAttribute $attribute): void {
        $this->attributes[] = $attribute;
    }

    public function toArray(): array {
        $array = array(
            'name'            => $this->name,
            'visibility'      => $this->visibility,
            'init'            => $this->init,
            'static'          => $this->static,
            'readonly'        => $this->readonly,
            'hasDefault'      => $this->hasDefault,
            'expression'      => $this->expression,
            'typehinttype'    => $this->typehinttype,
            'typehints'       => array(),
            'phpdoc'          => array(),
            'attributes'      => array(),
        );

        foreach (array('phpdoc', 'typehints', 'attributes') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

class PdffClass implements PdffHasProperty, PdffHasMethod, PdffHasAttribute, PdffHasConstant, PdffHasPhpdoc {
    private $name         = '';
    private $abstract     = false;
    private $final        = false;
    private $readonly     = false;
    private $extends      = '';

    private $methods      = array();
    private $constants    = array();
    private $properties   = array();
    private $uses         = array();
    private $usesOptions  = array(); // of strings
    private $attributes   = array();
    private $implements   = array();
    private $phpdoc       = array();

    public function __construct(string $name, bool $abstract, bool $final, bool $readonly, string $extends) {
        $this->name         = $name;
        $this->abstract     = $abstract;
        $this->final        = $final;
        $this->readonly     = $readonly;
        $this->extends      = $extends;
    }

    public function addClassConstant(string $name, PdffClassConstant $classConstant): void {
        $this->constants[$name] = $classConstant;
    }

    public function addProperty(string $name, PdffProperty $property): void {
        $this->properties[$name] = $property;
    }

    public function addMethod(string $name, PdffMethod $method): void {
        $this->methods[mb_strtolower($name)] = $method;
    }

    public function addAttribute(PdffAttribute $attribute): void {
        $this->attributes[] = $attribute;
    }

    public function addPhpdoc(PdffPhpdoc $phpdoc): void {
        $this->phpdoc[] = $phpdoc;
    }

    public function addImplements(PdffExtends $implement): void {
        $this->implements[] = $implement;
    }

    public function addUse(PdffExtends $use, array $options): void {
        $this->uses[] = $use;
        $this->usesOptions = array_merge($this->usesOptions, $options);
    }

    public function toArray(): array {
        $array = array(
            'name'            => $this->name,
            'abstract'        => $this->abstract,
            'final'           => $this->final,
            'readonly'        => $this->readonly,
            'extends'         => $this->extends,
            'constants'       => array(),
            'properties'      => array(),
            'methods'         => array(),
            'uses'            => array(),
            'usesOptions'     => $this->usesOptions,
            'attributes'      => array(),
            'implements'      => array(),
            'phpdoc'          => array(),
        );

        foreach (array('methods', 'constants', 'properties', 'attributes', 'uses', 'implements', 'phpdoc') as $type) {
            foreach ($this->$type as $name => $pdff) {
                $array[$type][$name] = $pdff->toArray();
            }
        }

        return $array;
    }
}

?>