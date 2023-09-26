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

namespace Exakat\Tasks\LoadFinal;

use Exakat\Analyzer\Analyzer;

class FinishExtends extends LoadFinal {
    private $times = 10;

    public function run(): void {
        $query = $this->newQuery('Extends to all parents');
        $query->atomIs(array('Class', 'Classanonymous', 'Interface'), Analyzer::WITHOUT_CONSTANTS)
            // Reach first parent
              ->outIs('EXTENDS')
              ->inIs('DEFINITION')
              ->raw(<<<GREMLIN
as("gotoallparents").emit( )
.repeat( __.out("EXTENDS")
           .in("DEFINITION")
           .hasLabel("Class", "Classanonymous", "Interface", "Trait")
// This seems to generate random results
//           .simplePath().from("gotoallparents").by('fullnspath')
           .simplePath()
        )
        .times($this->times)
        .hasLabel("Class", "Classanonymous", "Interface", "Trait")
GREMLIN
              )
              ->outIs('EXTENDS')
              ->not(
                  $query->side()
                        ->inIs('EXTENDS')
                        ->raw('where(eq("first"))')
              )
              ->hasNoLinkYet('EXTENDS', 'first')
              ->addEFrom('EXTENDS', 'first', array('extra' => 'true'))
              ->returnCount();
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $countExtends = $result->toInt();

        $query = $this->newQuery('Implements to all parents');
        $query->atomIs(array('Class', 'Classanonymous'), Analyzer::WITHOUT_CONSTANTS)
            // Reach first parent parents, which may be classes
              ->outIs(array('IMPLEMENTS', 'EXTENDS'))
              ->inIs('DEFINITION')
              ->raw(<<<GREMLIN
as("gotoallparents").emit( )
.repeat( __.out("EXTENDS", "IMPLEMENTS")
           .in("DEFINITION")
           .hasLabel("Class", "Classanonymous", "Interface")
// These steps makes the query go random
//           .simplePath().from("gotoallparents").by('fullnspath')
           .simplePath()
        )
        .times($this->times)
        .hasLabel("Class", "Classanonymous", "Interface")
        .where(neq("first"))
GREMLIN
              )
              ->outIs(array('IMPLEMENTS', 'EXTENDS'))
              ->not(
                  $query->side()
                        ->inIs(array('IMPLEMENTS', 'EXTENDS'))
                        ->raw('where(eq("first"))')
              )
              ->hasNoLinkYet('IMPLEMENTS', 'first')
              ->addEFrom('IMPLEMENTS', 'first', array('extra' => 'true'))
              ->returnCount();
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $countImplements = $result->toInt();

        $query = $this->newQuery('Use trait to all parents');
        $query->atomIs(array('Class', 'Classanonymous', 'Trait'), Analyzer::WITHOUT_CONSTANTS)
            // find all class first
              ->raw(<<<GREMLIN
optional(
    __.as("gotoallparentsclass").emit( )
.repeat( __.out("EXTENDS")
           .in("DEFINITION")
           .hasLabel("Class")
// These steps makes the query go random
//           .simplePath().from("gotoallparentsclass").by('fullnspath')
			.simplePath()
        )
        .times($this->times)
        .hasLabel("Class", "Trait")
    )
GREMLIN
              )
              ->raw(<<<GREMLIN
as("gotoallparents").emit( )
.repeat( __.out("USE").out("USE")
           .in("DEFINITION")
           .hasLabel("Trait")
//           .simplePath().from("gotoallparents").by("fullnspath")
           .simplePath()
        )
        .times($this->times)
        .hasLabel("Trait", "Class")
GREMLIN
              )
              ->outIs('USE')
              ->not(
                  $query->side()
                        ->inIs('USE')
                        ->raw('where(eq("first"))')
              )
              ->hasNoLinkYet('EXTENDS', 'first')
              ->addEFrom('USE', 'first', array('extra' => 'true'))
              ->returnCount();
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $countUse = $result->toInt();

        $count = $countExtends + $countImplements + $countUse;
        display("Created $count extends to parents");
    }
}

?>
