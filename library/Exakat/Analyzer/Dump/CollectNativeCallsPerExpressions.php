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

namespace Exakat\Analyzer\Dump;

class CollectNativeCallsPerExpressions extends AnalyzerHashHashResults {
    protected $analyzerName = 'NativeCallPerExpression';

    public function analyze(): void {
        $MAX_LOOPING = self::MAX_LOOPING;

        $this->atomIs('Sequence', self::WITHOUT_CONSTANTS)
              ->outIs('EXPRESSION')
              ->atomIsNot(array('Arrowfunction', 'Case', 'Catch', 'Class', 'Classanonymous', 'Closure', 'Default', 'Dowhile', 'Enum', 'Finally', 'For', 'Foreach', 'Function', 'Ifthen', 'Include', 'Namespace', 'Php', 'Return', 'Switch', 'Trait', 'Try', 'While'), self::WITHOUT_CONSTANTS)
              ->_as('results')
              ->raw(<<<GREMLIN
groupCount("m").by( __.emit( ).repeat( __.out({$this->linksDown}).not(hasLabel("Closure", "Arrowfunction", "Classanonymous")) ).times($MAX_LOOPING).hasLabel("Functioncall")
      .or( __.has("isPhp", true), __.has("isExt", true) )
      .count()
).cap("m")
GREMLIN
);
        $this->prepareQuery();
    }
}

?>
