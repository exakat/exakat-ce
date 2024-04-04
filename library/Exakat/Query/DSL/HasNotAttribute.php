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

use Exakat\Analyzer\Analyzer;

class HasNotAttribute extends DSL {
    public function run(): Command {
        $this->assertArguments(1, func_num_args(), __METHOD__);
        list($attributes) = func_get_args();

        $goToClass = $this->dslfactory->factory('goToClass');
        $return = $goToClass->run();

        $goToAllParents = $this->dslfactory->factory('goToAllParents');
        $return->add($goToAllParents->run(Analyzer::INCLUDE_SELF));

        $outIs = $this->dslfactory->factory('outIs');
        $return->add($outIs->run('ATTRIBUTE'));

        $is = $this->dslfactory->factory('is');
        $return->add($is->run('fullnspath', makeArray($attributes)));

        $not = $this->dslfactory->factory('not');

        return $not->run($return);
    }
}
?>
