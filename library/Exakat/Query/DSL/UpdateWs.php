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

class UpdateWs extends DSL {
    public function run(): Command {
        list($values) = func_get_args();

        // @todo : set a array (j["opening"][0] = 1)
        // @todo : set a piece of code or a relative value  (                        j["opening"] = j["opening"].substring(3, j["opening"].length());)
        // @todo : get a piece of code
        // @todo : remove a piece of code (j["closing"].remove())
        $code = array();
        $arguments = array();
        foreach ($values as $name => $value) {
            $code[] = "j[\"$name\"] = j[\"$name\"].replaceAll($value[0], $value[1]);";
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

        return new Command($gremlin, $arguments);
    }
}
?>
