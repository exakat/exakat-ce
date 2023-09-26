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

namespace Exakat\Analyzer\Dump;


class CollectClassChanges extends AnalyzerTable {
    protected string $analyzerName = 'classChanges';

    protected string $analyzerTable = 'classChanges';

    protected string $analyzerSQLTable = <<<'SQL'
CREATE TABLE classChanges (  
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    changeType   STRING,
    name         STRING,
    parentClass  STRING,
    parentValue  STRING,
    childClass   STRING,
    childValue   STRING
                    )
SQL;

    public function dependsOn(): array {
        return array('Complete/OverwrittenProperties',
                     'Complete/OverwrittenMethods',
                    );
    }


    public function analyze(): void {
        $total = 0;

        // Comparing Class constant : values, visibility

        // Class constants with different values
        $this->atomIs('Constant', self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->savePropertyAs('fullcode', 'name')
              ->inIs('NAME')
              ->outIs('VALUE')
              ->savePropertyAs('fullcode', 'default1')
              ->inIs('VALUE')

              ->inIs('CONST')
              ->inIs('CONST')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)

              ->savePropertyAs('fullcode', 'class1')
              ->goToAllParents(self::EXCLUDE_SELF)
              ->savePropertyAs('fullcode', 'class2') // another class

              ->outIs('CONST')
              ->outIs('CONST')

              ->outIs('NAME')
              ->samePropertyAs('fullcode', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')

              ->outIs('VALUE')
              ->notSamePropertyAs('fullcode', 'default1', self::CASE_SENSITIVE) // test
              ->savePropertyAs('fullcode', 'default2') // collect

              ->selectMap(array(
                  'type' => '"Constant Value"',
                  'name' => 'name',
                  'parent' => 'class2',
                  'parentValue' => "name + ' = ' + default2",
                  'class' => 'class1',
                  'classValue' => "name + ' = ' + default1",
              ));
        $this->prepareQuery();

        // Class constants with different visibility
        $this->atomIs('Constant', self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->savePropertyAs('fullcode', 'name')
              ->inIs('NAME')

              ->inIs('CONST')
              ->savePropertyAs('visibility', 'visibility1')
              ->inIs('CONST')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)

              ->savePropertyAs('fullcode', 'class1')
              ->goToAllParents(self::EXCLUDE_SELF)
              ->savePropertyAs('fullcode', 'class2') // another class

              ->outIs('CONST')
              ->notSamePropertyAs('visibility', 'visibility1', self::CASE_SENSITIVE) // test
              ->savePropertyAs('visibility', 'visibility2') // collect
              ->outIs('CONST')

              ->outIs('NAME')
              ->samePropertyAs('fullcode', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')

              ->selectMap(array(
                  'type' => '"Constant Visibility"',
                  'name' => 'name',
                  'parent' => 'class2',
                  'parentValue' => "visibility2 + ' ' + name",
                  'class' => 'class1',
                  'classValue' => "visibility1 + ' ' + name",
              ));

        $this->prepareQuery();

        // Comparing Methods : return type, visibility, argument's type, default, name

        // Methods with different signatures : argument's type, default, name
        // Upgrade this with separate queries for each element.

        $this->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->savePropertyAs('fullcode', 'name')
              ->inIs('NAME')
              ->collectArguments('signature1')

              ->inIs('METHOD')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullcode', 'class1')

              ->back('first')
              ->outIs('OVERWRITE')

              ->outIs('NAME')
              ->samePropertyAs('fullcode', 'name', self::CASE_SENSITIVE)
              ->inIs('NAME')
              ->collectArguments('signature2')
              ->notSameVariableAs('signature1', 'signature2')

              ->inIs('METHOD')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullcode', 'class2') // another class

              ->selectMap(array(
                  'type' => '"Method Signature"',
                  'name' => 'name',
                  'parent' => 'class2',
                  'parentValue' => "\"function \" + name + '(' + signature2.join(', ') + ')'",
                  'class' => 'class1',
                  'classValue' => "\"function \" + name + '(' + signature1.join(', ') + ')'",
              ));
        $this->prepareQuery();

        // Methods with different visibility
        $this->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullnspath', 'fnp')
              ->savePropertyAs('visibility', 'visibility1')
              ->inIs(array('METHOD', 'MAGICMETHOD'))
              ->savePropertyAs('fullcode', 'name1')
              ->back('first')
              ->inIs('OVERWRITE')
              ->savePropertyAs('visibility', 'visibility2')
              ->notSameVariableAs('visibility1', 'visibility2')
              ->inIs('METHOD')
              ->savePropertyAs('fullcode', 'name2')
              ->selectMap(array(
                  'type' => '"Method Visibility"',
                  'name' => 'fnp.tokenize(\'::\')[1]',
                  'parent' => 'name1',
                  'parentValue' => "visibility2 + ' ' + fnp.tokenize('::')[1]",
                  'class' => 'name2',
                  'classValue' => "visibility1 + ' ' + fnp.tokenize('::')[1]",
              ));
        $this->prepareQuery();

        // Methods with different return types
        $this->atomIs(array('Method', 'Magicmethod'), self::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullnspath', 'method1')
              ->collectTypehints('returntype1')
              ->inIs(array('METHOD', 'MAGICMETHOD'))
              ->savePropertyAs('fullcode', 'name1')
              ->back('first')
              ->inIs('OVERWRITE')
              ->savePropertyAs('fullnspath', 'method2')
              ->collectTypehints('returntype2')
              ->notSameVariableAs('returntype1', 'returntype2')
              ->inIs('METHOD')
              ->savePropertyAs('fullcode', 'name2')
              ->selectMap(array(
                  'type' => '"Method Returntype"',
                  'name' => 'method1.tokenize(\'::\')[1]',
                  'parent' => 'name1',
                  'parentValue' => "method2.tokenize('::')[1] + ' : ' + returntype2",
                  'class' => 'name2',
                  'classValue' => "method1.tokenize('::')[1] + ' : ' + returntype1",
              ));
        $this->prepareQuery();

        // Comparing Properties
        // default value, visibility, typehint

        // Property with different default value
        $this->atomIs('Propertydefinition', self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->savePropertyAs('fullcode', 'name')
              ->inIs('NAME')
              ->outIs('DEFAULT')
              ->hasNoIn('RIGHT') // find an explicit default
              ->savePropertyAs('fullcode', 'default1')
              ->inIs('DEFAULT')
              ->inIs('PPP')
              ->inIs('PPP')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullcode', 'class1')

              ->back('first')
              ->outIs('OVERWRITE')

              ->outIs('DEFAULT')
              ->notSamePropertyAs('fullcode', 'default1', self::CASE_SENSITIVE)
              ->savePropertyAs('fullcode', 'default2')
              ->inIs('DEFAULT')

              ->inIs('PPP')
              ->inIs('PPP')
              ->savePropertyAs('fullcode', 'class2')

              ->selectMap(array(
                  'type' => '"Method Default"',
                  'name' => 'name',
                  'parent' => 'class2',
                  'parentValue' => "name + ' = ' + default2",
                  'class' => 'class1',
                  'classValue' => "name + ' = ' + default1",
              ));
        $this->prepareQuery();

        // Property with different visibility
        $this->atomIs('Propertydefinition', self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->savePropertyAs('fullcode', 'name')
              ->inIs('NAME')
              ->inIs('PPP')
              ->savePropertyAs('visibility', 'visibility1')
              ->inIs('PPP')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullcode', 'class1')
              ->goToAllParents(self::EXCLUDE_SELF)
              ->savePropertyAs('fullcode', 'class2')

              ->outIs('PPP')
              ->notSamePropertyAs('visibility', 'visibility1', self::CASE_SENSITIVE)
              ->savePropertyAs('visibility', 'visibility2')
              ->outIs('PPP')
              ->samePropertyAs('fullcode', 'name', self::CASE_SENSITIVE)

              ->selectMap(array(
                  'type' => '"Method Visibility"',
                  'name' => 'name',
                  'parent' => 'class2',
                  'parentValue' => 'visibility2 + name',
                  'class' => 'class1',
                  'classValue' => 'visibility1 + name',
              ));
        $this->prepareQuery();

        // Property with different typehint
        $this->atomIs('Propertydefinition', self::WITHOUT_CONSTANTS)
              ->outIs('NAME')
              ->savePropertyAs('fullcode', 'name')
              ->inIs('NAME')
              ->inIs('PPP')
              ->collectTypehints('types1')
              ->inIs('PPP')
              ->atomIs(self::CLASSES_ALL, self::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullcode', 'class1')

              ->back('first')
              ->inIs('OVERWRITE')

              ->inIs('PPP')
              ->collectTypehints('types2')
              ->notSameVariableAs('types1', 'types2')
              ->inIs('PPP')
              ->savePropertyAs('fullcode', 'class2')

              ->selectMap(array(
                  'type' => '"Method Type"',
                  'name' => 'name',
                  'parent' => 'class2',
                  'parentValue' => "types2.join('|') + ' ' + name",
                  'class' => 'class1',
                  'classValue' => "types1.join('|') + ' ' + name",
              ));
        $this->prepareQuery();
    }
}

?>
