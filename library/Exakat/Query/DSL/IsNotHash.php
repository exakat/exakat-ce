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

use Exakat\Query\Query;
use Exakat\Analyzer\Analyzer;
use Exakat\Exceptions\WrongNumberOfArguments;

class IsNotHash extends DSL {
    public function run(): Command {
        switch (func_num_args()) {
            case 3 :
                list($property, $hash, $index) = func_get_args();
                $case = Analyzer::CASE_SENSITIVE;
                break;

            case 4 :
                list($property, $hash, $index, $case) = func_get_args();
                assert(in_array($case, array(Analyzer::CASE_INSENSITIVE, Analyzer::CASE_SENSITIVE)));
                break;

            default:
                throw new WrongNumberOfArguments('Wrong number of arguments for ' . __METHOD__);
        }

        if (empty($hash)) {
            return new Command(Query::NO_QUERY);
        }

        assert($this->assertProperty($property));

        // Cannot make this to work with contains / in (Classes/CouldBeProtectedProperty)
        if ($case === Analyzer::CASE_INSENSITIVE) {
            return new Command("has(\"$property\").filter{ x = ***[$index].collect{ it.toLowerCase() }; [it.get().value(\"$property\").toLowerCase()].intersect(x) == []; }", array($hash));
        } else {
            return new Command("has(\"$property\").filter{ [it.get().value(\"$property\")].intersect(***[$index]) == []}", array($hash));
        }
    }
}
?>
