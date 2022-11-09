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

interface StubsInterface {
    public function getFile(): array;
    public function getFunctionList(): array;
    public function getConstantList(): array;
    public function getFunctionsArgsInterval(): array;
    public function getInterfaceList(): array;
    public function getEnumList(): array;
    public function getTraitList(): array;
    public function getClassList(): array;
    public function getAbstractClassList(): array;
    public function getClassConstantList(): array;
    public function getEnumCasesList(): array;
    public function getClassPropertyList(): array;
    public function getClassStaticPropertyList(): array;
    public function getClassMethodList(): array;
    public function getClassStaticMethodList(): array;
    public function getPropertyList(): array;
    public function getMethodList(): array;
    public function getInterfaceMethodsNameAndCount(): array;
    public function getFinalClasses(): array;
    public function getFinalClassConstants(): array;
    public function getFinalClassMethods(): array;
    public function getFunctionNamesList(): array;
    public function getClassMethodNamesList(): array;
    public function getNamespaceList(): array;
    public function getConstructorsArgsInterval(): array;
    public function getMethodsArgsInterval(): array;
    public function getClassImplementingList(): array;
    public function getFunctionsReferenceArgs(): array;
    public function getPropertyListWithVisibility(string $visibility): array;
    public function getConstantListWithVisibility(string $visibility): array;
    public function getMethodListWithVisibility(string $visibility): array;
    public function getFunctionParameterNames(array $functions): array;
    public function getNoNullReturningFunctions(): array;
    public function getNativeMethodReturn(): array;
    public function getVoidReturningFunctions(): array;
}