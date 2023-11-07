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

use Exakat\Exceptions\QueryException;

class GoToALineInCode extends DSL {
    public function run(): Command {
    	switch(func_num_args()) {
    		case 0:
    			$limit = 1;
    			break;
    			
    		case 1:
    			$limit = (int) func_get_arg(0);
    			break;
    			
    		default:
    			throw new QueryException('Wrong number of arguments for ' . __METHOD__);
    	}

        return new Command('local(__.out("DEFINITION").limit('.$limit.'))');
    }
}
?>
