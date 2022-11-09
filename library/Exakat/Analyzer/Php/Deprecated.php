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


namespace Exakat\Analyzer\Php;

use Exakat\Analyzer\Analyzer;

class Deprecated extends Analyzer {
    public function analyze(): void {
        $ini = (object) $this->loadIni('deprecated.ini');

        $ini->functions = array_filter($ini->functions);
        $ini->functions = array_unique($ini->functions);
        $ini->functions = array_values($ini->functions);
        $functions = makeFullNsPath($ini->functions);

        // direct call to an old global function
        $this->atomFunctionIs($functions);
        $this->prepareQuery();

        // fallback call to an old global function
        $this->atomIs('Functioncall')
             ->hasNoIn('DEFINITION')
             ->has('fullnspath')
             ->raw(<<<'GREMLIN'
filter{
    it.get().value('fullnspath').tokenize('\\').size() > 1;
}.filter{
    name = '\\' + it.get().value('fullnspath').tokenize('\\').last();
    name in ***;
}
GREMLIN
                 , $functions);
        $this->prepareQuery();
    }
}

?>
