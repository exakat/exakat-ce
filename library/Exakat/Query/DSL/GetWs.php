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

class GetWs extends DSL {
    public function run(): Command {
        list($values) = func_get_args();

        // @todo : retrieve a single element from an array ["x"][0]
        // @todo check the elements from WS first
        // @todo : initialize the variables
        $code = array();
        $arguments = array();
        foreach($values as $element => $variable) {
            $code[] = "$variable = j[\"$element\"];";
        }

        $codeGremlin = implode(PHP_EOL, $code);

        $gremlin = <<<GREMLIN
sideEffect{ 
    json = new groovy.json.JsonSlurper();
    j = json.parseText(it.get().property("ws").value().toString());
    $codeGremlin
    
    it.get().property("ws", groovy.json.JsonOutput.toJson(j));
}
GREMLIN;

        return new Command($gremlin);
    }
}
?>
