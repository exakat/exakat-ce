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

namespace Exakat\Analyzer\Common;

use Exakat\Analyzer\Analyzer;

abstract class UsesFrameworkConstant extends Analyzer {
    protected array $constants   = array();

    public function analyze(): void {
        $analyzerId = null;

        if (!empty($this->constants[0])) {
            $constants = makeFullNsPath($this->constants, \FNP_CONSTANT);

            if (!empty($constants)) {
                $constantsUsage = new ConstantUsage();
                $constantsUsage->setAnalyzer(get_class($this));
                $constantsUsage->setConstants($constants);
                $analyzerId = $constantsUsage->init($analyzerId);
                $constantsUsage->run();

                $this->rowCount        += $constantsUsage->getRowCount();
                $this->processedCount  += $constantsUsage->getProcessedCount();
                $this->queryCount      += $constantsUsage->getQueryCount();
                $this->rawQueryCount   += $constantsUsage->getRawQueryCount();
            }
        }
    }
}

?>
