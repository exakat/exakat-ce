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


namespace Exakat\Query\DSL;


class CollectMethods extends DSL {
    public const METHOD_ALL = 1;
    public const METHOD_ABSTRACT = 2;
    public const METHOD_CONCRETE = 3;

    public function run(): Command {
        switch (func_num_args()) {
            case 1 :
                $variable = func_get_arg(0);
                $methods = self::METHOD_ALL;
                break;

            case 2:
                $variable = func_get_arg(0);
                $methods = func_get_arg(1);
                break;

            default:
                assert(false, 'collectMethods needs 1 or 2 arguments : ' . func_num_args() . ' were provided.');
        }

        $this->assertVariable($variable, self::VARIABLE_WRITE);

        switch ($methods) {
            default:
            case self::METHOD_ALL:
                $final = "{$variable} = {$variable}.keySet();";
                break;

            case self::METHOD_ABSTRACT:
                $final = "{$variable} = {$variable}.findAll{ k,v -> v == true}.keySet();";
                break;

            case self::METHOD_CONCRETE:
                $final = "{$variable} = {$variable}.findAll{ k,v -> v == false}.keySet();";
                break;
        }

        // methods are collected from current class till above.
        // No trait involved
        // only the first method found is used (others, with same name are ignored)
        // abstract option is included
        $command = new Command(<<<GREMLIN
 sideEffect{ {$variable} = [:]; }
.where(
    __
    .where( 
        __.out("METHOD", "MAGICMETHOD")
          .sideEffect{ if({$variable}[it.get().value("lccode")] == null) { {$variable}[it.get().value("lccode")] = it.get().properties("abstract").any(); } }
          .out("NAME")
          .fold() 
        )
    .where( 
        __.out("USE").out("USE").in("DEFINITION")
          .out("METHOD", "MAGICMETHOD")
          .sideEffect{ if({$variable}[it.get().value("lccode")] == null) { {$variable}[it.get().value("lccode")] = it.get().properties("abstract").any(); } }
          .out("NAME")
          .fold() 
        )
    .where(
        __.out("EXTENDS").in("DEFINITION")
          .where( 
              __.out("USE").out("USE").in("DEFINITION")
                .out("METHOD", "MAGICMETHOD")
                .sideEffect{ if({$variable}[it.get().value("lccode")] == null) { {$variable}[it.get().value("lccode")] = it.get().properties("abstract").any(); } }
                .out("NAME")
                .fold() 
            )
        .where( 
            __
              .out("METHOD", "MAGICMETHOD")
              .sideEffect{ if({$variable}[it.get().value("lccode")] == null) { {$variable}[it.get().value("lccode")] = it.get().properties("abstract").any(); } }
              .out("NAME")
              .fold() 
            )
            .fold()
    )
)
.sideEffect{ $final }
GREMLIN
        );
        return $command;
    }
}
?>
