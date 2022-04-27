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

namespace Exakat\Stubs;


class StubIni extends Stubs implements StubsInterface {
    private $stubFile    = '';
    private $stub        = array();

    public function __construct(string $stubFile) {
        $this->stubFile = $stubFile;

        assert(file_exists($stubFile), "No data for $stubFile");
        $this->stub = parse_ini_file($stubFile);
    }

    public function getFile(): array {
        return array( basename($this->stubFile) );
    }

    public function getFunctionList(): array {
        return $this->stub->functions ?? array();
    }

    public function getConstantList(): array {
        return $this->stub->constants ?? array();
    }

    public function getFunctionsArgsInterval(): array {
        return array();
    }

    public function getInterfaceList(): array {
        return $this->stub->interfaces ?? array();
    }

    public function getTraitList(): array {
        return $this->stub->traits ?? array();
    }

    public function getClassList(): array {
        return $this->stub->classes ?? array();
    }

    public function getClassConstantList(): array {
        return $this->stub->staticConstants ?? array();
    }

    public function getClassPropertyList(): array {
        return $this->stub->staticProperties ?? array();
    }

    public function getClassMethodList(): array {
        return $this->stub->methods ?? array();
    }


    public function getPropertyList(): array {
        return $this->stub->properties ?? array();
    }

    public function getMethodList(): array {
        return $this->stub->methods ?? array();
    }

    public function getFinalClasses(): array {
        return array();
    }

    public function getFinalClassConstants(): array {
        return array();
    }

    public function getFunctionNamesList(): array {
        return array();
    }

    public function getClassMethodNamesList(): array {
        return array();
    }

    public function getNamespaceList(): array {
        return array();
    }

    public function getConstructorsArgsInterval(): array {
        return array();
    }

    public function getMethodsArgsInterval(): array {
        return array();
    }

    public function getEnumList(): array {
        return array();
    }

    public function getClassStaticPropertyList(): array {
        return array();
    }

    public function getClassStaticMethodList(): array {
        return array();
    }

    public function getEnumCasesList(): array {
        return array();
    }

    public function getInterfaceMethodsNameAndCount(): array {
        return array();
    }
}

?>
