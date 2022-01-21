<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


namespace Exakat\Analyzer\Classes;

use Exakat\Analyzer\Analyzer;

class UndefinedProperty extends Analyzer {
    public function dependsOn(): array {
        return array('Complete/CreateMagicProperty',
                     'Classes/HasMagicProperty',
                     'Complete/SetClassRemoteDefinitionWithTypehint',
                     'Complete/SetClassRemoteDefinitionWithLocalNew',
                     'Complete/VariableTypehint',
                    );
    }

    public function analyze(): void {
//        $includedProperties = $this->readPdff();
        $includedProperties = array();
        
        // only for calls internal to the class. External calls still needs some work
        // static properties without a definition
        $this->atomIs(array('Member', 'Staticproperty'))

            // do not have __get/__set set up (trait or parents or else) 
            ->not(
                $this->side()
                     ->goToClass()
                     ->analyzerIs('Classes/HasMagicProperty')
            )

            // Do not extends stdclass 
            ->not(
                $this->side()
                     ->goToClass()
                     ->goToAllParents(self::INCLUDE_SELF)
                     ->outIs('EXTENDS')
                     ->is('fullnspath', array('\\stdclass'))
            )

            // Do not use  #[allowdynamicproperties ]
            ->hasNotAttribute('\\allowdynamicproperties')

             // not a property in a parent class in the code 
             ->inIs('DEFINITION')
             ->atomIs('Virtualproperty')
             ->not(
                $this->side()
                     ->outIs('OVERWRITE')
                     ->atomIs('Propertydefinition')
                     ->inIs('PPP')
                     ->isNot('visibility','private')
             )

             // Not a property in a included component
             ->back('first')
             ->not(
                $this->side()
                     ->outIs('MEMBER')
                     ->savePropertyAs('fullcode', 'name')
                     ->goToClass()
                     ->goToAllParents(self::INCLUDE_SELF) 
                     ->outIs('EXTENDS')
                     ->savePropertyAs('fullnspath', 'fnp')

                     ->raw(<<<GREMLIN
    filter{ 
        property = fnp + '::\$' + name; 
        property in ***;
    }
GREMLIN
, $includedProperties
)
             )
             ->back('first');
        $this->prepareQuery();

        // case of public access
        $this->atomIs(array('Member', 'Staticproperty'))
             ->hasNoIn('DEFINITION')
             ->outIs(array('OBJECT', 'CLASS'))
             ->inIs('DEFINITION')
             ->inIsIE('NAME')
             ->outIs('TYPEHINT')
             ->inIs('DEFINITION')
             ->back('first');
        $this->prepareQuery();
             

    }

    private function readPdff() : array {
        $return = array();

        $a = json_decode(file_get_contents($this->config->dir_root.'/php.pdff'));
        $return[] = $this->readPdffFile($a);

        $list = array('wordpress', 'bbpress', 'buddypress', 'woocommerce', 'acf');
        foreach($list as $l) {
            $a = json_decode(file_get_contents('/Users/famille/Desktop/analyzeG3/projects/'.$l.'/pdff'));
            $return[] = $this->readPdffFile($a);
        }
        
        return array_merge(...$return);
    }
    
    private function readPdffFile($pdff) : array {
        $return = array();
        $classes = ($pdff->versions->{'*'} ?? $pdff->versions->{'8.1.0'})->{'\\'}->classes;
        foreach($classes as $class) {
            $fnp = '\\'.strtolower($class->name);
            $return[] = $this->getProperties($class, $fnp);
            
            $extends = trim($class->extends, '\\');
            while (!empty($extends) && isset($pdff->versions->{'*'}->{'\\'}->classes->{$extends}))  {
                $return[] = $this->getProperties($pdff->versions->{'*'}->{'\\'}->classes->{$extends}, $fnp);
                
                $extends = trim($pdff->versions->{'*'}->{'\\'}->classes->{$extends}->extends, '\\');
            }
        }
        
        $return = array_merge(...$return);

        return $return;
    }
    
    private function getProperties($class, $fnp) : array {
        $return = [];
            foreach($class->properties as $property) {
                $return[] = "$fnp::$property->name";
            }
            
        return $return;
    }

    private function merges($pdff1, $pdff2) {
        $return = new \stdClass;
        foreach($pdff1 as $key => $value) {
            if (isset($pdff2->{$key})) {
                switch($key) {
                    case 'versions':
                        $return->{$key} = new \Stdclass;
                        $return->{$key}->{'*'} = new \Stdclass;
                        
                        foreach($pdff1->versions->{'*'} as $ns => $namespace) {
                            $pdff1->versions->{'*'}->{$ns} = $this->merges($pdff1->{$key}->{'*'}->{$ns}, $pdff2->{$key}->{'*'}->{$ns});
                        }
                        break;
                    
                    default:
                        print "No recursion for key $key\n";
                }
            } else {
                $return->{$key} = $value;
            }
        }

        foreach($pdff2 as $key => $value) {
            print "KEy 2 : $key\n";
            if (!isset($pdff1->{$key})) {
                $return->{$key} = $value;
            }
        }
        
        return $return;
    }
}

?>
