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

namespace Exakat\Analyzer\Complete;

use Exakat\Analyzer\Analyzer;

class PhpExtStubPropertyMethod extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/CreateDefaultValues',
                        // @todo use variableTypehints
                    );
    }

    public function analyze(): void {
        $rulesets = exakat('rulesets');
        $exts = $rulesets->listAllAnalyzer('Extensions');

        $properties = array();
        $methods    = array();
        // @todo : find a way to collect some values across all available stubs (php, stub, exts)
        $exts = array('bcmath',
'bz2',
'calendar',
'core',
'ctype',
'curl',
'date',
'dba',
'dom',
'enchant',
'exif',
'ffi',
'fileinfo',
'filter',
'ftp',
'gd',
'gettext',
'gmp',
'hash',
'iconv',
'intl',
'json',
'ldap',
'libxml',
'mbstring',
'mysqli',
'odbc',
'openssl',
'pcntl',
'pcre',
'pdo',
'pdo_dblib',
'pdo_mysql',
'pdo_odbc',
'pdo_pgsql',
'pdo_sqlite',
'pgsql',
'phar',
'posix',
'pspell',
'readline',
'reflection',
'session',
'shmop',
'simplexml',
'soap',
'sockets',
'sodium',
'spl',
'sqlite3',
'standard',
'sysvmsg',
'sysvsem',
'sysvshm',
'tidy',
'tokenizer',
'xml',
'xmlreader',
'xmlwriter',
'xsl',
'zend opcache',
'zlib',
);

        foreach ($exts as $ext) {
            $ini = $this->loadPdff($ext . '.pdff');

            $methodList = $ini->getMethodList();
            foreach ($methodList as $fullMethod) {
                list($class, $method) = explode('::', $fullMethod, 2);
                array_collect_by($methods, mb_strtolower($method), makeFullNsPath($class));
            }

            $propertyList = $ini->getPropertyList();
            foreach ($propertyList as $fullProperty) {
                list($class, $property) = explode('::', $fullProperty, 2);
                array_collect_by($properties, ltrim($property, '$'), makeFullNsPath($class));
            }
        }

        // $mysqli->$p with typehints
        $this->atomIs('Member')
             ->isNot('isExt', true)
             ->outIs('MEMBER')
             ->fullcodeIs(array_keys($properties))
             ->savePropertyAs('fullcode', 'property')
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs(array('Variableobject', 'Member', 'Staticproperty'))
             ->goToTypehint()
             ->atomIs(self::STATIC_NAMES)
             ->isHash('fullnspath', $properties, 'property')
             ->back('first')
             ->property('isExt', true);
        $this->prepareQuery();

        // $mysqli->$p with local new
        $this->atomIs('Member')
             ->isNot('isExt', true)
             ->outIs('MEMBER')
             ->fullcodeIs(array_keys($properties))
             ->savePropertyAs('fullcode', 'property')
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs('Variableobject')
             ->filter(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('DEFAULT')
                      ->atomIs('New')
                      ->outIs('NEW')
                      ->isHash('fullnspath', $properties, 'property')
             )
             ->back('first')
             ->property('isExt', true);
        $this->prepareQuery();

        // $mysqli->m() with typehints
        $this->atomIs('Methodcall')
             ->isNot('isExt', true)
             ->outIs('METHOD')
             ->outIs('NAME')
             ->fullcodeIs(array_keys($methods), self::CASE_INSENSITIVE)
             ->savePropertyAs('fullcode', 'method')
             ->variableToLowerCase('method')
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs('Variableobject')
             ->goToTypehint()
             ->atomIs(self::STATIC_NAMES)
             ->isHash('fullnspath', $methods, 'method')
             ->back('first')
             ->property('isExt', true);
        $this->prepareQuery();

        // $mysqli->m() with local new
        $this->atomIs('Methodcall')
             ->isNot('isExt', true)
             ->outIs('METHOD')
             ->outIs('NAME')
             ->fullcodeIs(array_keys($methods), self::CASE_INSENSITIVE)
             ->savePropertyAs('fullcode', 'method')
             ->variableToLowerCase('method')
             ->back('first')

             ->outIs('OBJECT')
             ->atomIs('Variableobject')
             ->filter(
                 $this->side()
                      ->inIs('DEFINITION')
                      ->outIs('DEFAULT')
                      ->atomIs('New')
                      ->outIs('NEW')
                      ->isHash('fullnspath', $methods, 'method')
             )
             ->back('first')
             ->property('isExt', true);
        $this->prepareQuery();
    }
}

?>
