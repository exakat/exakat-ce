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

use Exception;

class GroupCount extends DSL {
    public function run(): Command {
        switch(func_num_args()) {
            case 0:
                return new Command('groupCount()');

            case 1:
                list($column) = func_get_args();

                if ($column === 'label') {
                    return new Command('groupCount().by(label)');
                }

                if ($column === 'id') {
                    return new Command('groupCount().by(id)');
                }

                if (is_string($column)) {
                    if (substr($column, 0, 2) === '__') {
                        return new Command("groupCount().by($column)");
                    } else {
                        return new Command("groupCount().by(\"$column\")");
                    }
                }

                if (is_array($column)) {
                    return new Command('groupCount().by("' . implode('").by("', $column) . '")');
                }

                throw new Exception('Not supported type');

            default:
                $this->assertArguments(-1, func_num_args(), __METHOD__);
        }
    }
}
?>
