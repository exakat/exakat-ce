<?php declare(strict_types = 1);
/*
 * Copyright 2012-2024 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


namespace Exakat\Query\DSL;


class ProcessCyclomaticComplexity extends DSL {
    public function run(): Command {
        assert(func_num_args() === 1, 'Wrong number of argument for ' . __METHOD__);

        list($name) = func_get_args();

        $this->assertVariable($name, self::VARIABLE_WRITE);

        $MAX_LOOPING = self::$MAX_LOOPING;
        $linksDown   = self::$linksDown;
        return new Command(<<<GREMLIN
project("$name").by(
    __.emit().repeat( __.out($linksDown)).times($MAX_LOOPING).coalesce(
        __.hasLabel(
            "Ifthen", "Case", "Default", "Foreach", "For" ,"Dowhile", "While", "Continue", 
            "Catch", "Finally", "Throw", 
            "Ternary", "Coalesce"
            ),
    __.hasLabel("Ifthen").out("THEN", "ELSE"),
    __.hasLabel("Return").sideEffect{ ranked = it.get().value("rank");}.in("EXPRESSION").coalesce( __.filter{ it.get().value("count") != ranked + 1;},
                                                                                                   __.not(where(__.in("BLOCK").hasLabel("Function"))))
    ).count()
)
GREMLIN
        );
    }
}
?>
