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

namespace Exakat\Helpers;

use Exakat\Graph\Graph;

class Called {
    private Graph $graph;

    // @todo : turn to an helper class
    private ?array $calledFunctions       = null;
    private ?array $calledStaticMethods   = null;
    private ?array $calledMethods         = null;
    private ?array $calledClasses         = null;
    private ?array $calledInterfaces      = null;
    private ?array $calledTraits          = null;
    private ?array $calledEnums           = null;
    private ?array $calledNamespaces      = null;
    private ?array $calledDirectives      = null;

    public function __construct(Graph $graph) {
        $this->graph = $graph;
    }

    public function getCalledFunctions(): array {
        if ($this->calledFunctions === null) {
            $calls = $this->graph
                          ->query('g.V().hasLabel("Functioncall").values("fullnspath").unique()')
                          ->toArray();

            $this->calledFunctions = $calls;
        }

        return $this->calledFunctions;
    }

    public function getCalledStaticmethods(): array {
        if ($this->calledStaticMethods === null) {
            $calls = $this->graph->query('g.V().hasLabel("Staticmethodcall").values("fullnspath").unique()')
                          ->toArray();

            $this->calledStaticMethods = $calls;
        }

        return $this->calledStaticMethods;
    }

    public function getCalledmethods(): array {
        if ($this->calledMethods === null) {
            $calls = $this->graph->query('g.V().hasLabel("Methodcall").values("fullnspath").unique()')
                          ->toArray();

            $this->calledMethods = $calls;
        }

        return $this->calledMethods;
    }

    public function getCalledClasses(): array {
        if ($this->calledClasses === null) {
            $news = $this->graph->query('g.V().hasLabel("New").out("NEW").values("fullnspath").unique()')
                         ->toArray();
            $staticcalls = $this->graph->query('g.V().hasLabel("Staticconstant", "Staticmethodcall", "Staticproperty", "Instanceof", "Catch").out("CLASS").values("fullnspath").unique()')
                                ->toArray();
            $typehints   = $this->graph->query('g.V().hasLabel("Parameter").out("TYPEHINT").values("fullnspath").unique()')
                                ->toArray();
            $returntype  = $this->graph->query('g.V().hasLabel("Method", "Magicmethod", "Closure", "Function", "Arrowfunction").out("RETURNTYPE").values("fullnspath").unique()')
                                ->toArray();
            $extendstype  = $this->graph->query('g.V().hasLabel("Class", "Classanonymous").out("EXTENDS", "IMPLEMENTS", "USE").optional(out("USE")).values("fullnspath").unique()')
                                ->toArray();
            $this->calledClasses = array_unique(array_merge($staticcalls,
                $news,
                $typehints,
                $returntype,
                $extendstype,
            ));
        }

        return $this->calledClasses;
    }

    public function getCalledInterfaces(): array {
        if ($this->calledInterfaces === null) {
            $this->calledInterfaces = $this->graph->query('g.V().hasLabel("Analysis").has("analyzer", "Interfaces/InterfaceUsage").out("ANALYZED").values("fullnspath")')
                                           ->toArray();

            $this->calledInterfaces = array_unique($this->calledInterfaces);
        }

        return $this->calledInterfaces;
    }

    public function getCalledEnums(): array {
        if ($this->calledEnums === null) {
            $this->calledEnums = $this->graph->query('g.V().hasLabel("Analysis").has("analyzer", "Php/EnumUsage").out("ANALYZED").values("fullnspath").unique()')
                                           ->toArray();

            $this->calledEnums = array_unique($this->calledEnums);
        }

        return $this->calledEnums;
    }

    public function getCalledTraits(): array {
        if ($this->calledTraits === null) {
            $query = <<<'GREMLIN'
g.V().hasLabel("Analyzer")
     .has("analyzer", "Traits/TraitUsage")
     .out("ANALYZED")
     .values("fullnspath")
GREMLIN;
            $this->calledTraits = $this->graph->query($query)
                                       ->toArray();

            $this->calledTraits = array_unique($this->calledTraits);
        }

        return $this->calledTraits;
    }

    public function getCalledNamespaces(): array {
        if ($this->calledNamespaces === null) {
            $query = <<<'GREMLIN'
g.V().hasLabel("Namespace")
     .values("fullnspath")
     .unique()
GREMLIN;
            $this->calledNamespaces = $this->graph->query($query)
                                           ->toArray();
        }

        return $this->calledNamespaces;
    }

    public function getCalledDirectives(): array {
        if ($this->calledDirectives === null) {
            $query = <<<'GREMLIN'
g.V().hasLabel("Analysis")
     .has("analyzer", "Php/DirectivesUsage")
     .out("ANALYZED")
     .out("ARGUMENT")
     .has("rank", 0)
     .hasLabel("String")
     .has("noDelimiter")
     .values("noDelimiter")
     .unique()
GREMLIN;
            $this->calledDirectives = $this->graph->query($query)
                                           ->toArray();
        }

        return $this->calledDirectives;
    }
}

?>