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

class JsonSerializeReturnType extends Analyzer {
    public function analyze(): void {
        // class x implements jsonserialize { function jsonseralize() : int {}}
        /*
            JsonSerializable::jsonSerialize()
            SessionHandlerInterface::open()
            SessionHandlerInterface::close()
            SessionHandlerInterface::read()
            SessionHandlerInterface::write()
            SessionHandlerInterface::destroy()
            SessionHandlerInterface::gc()

        */
        // @todo : case for    ReflectionClass::*()
        // @todo : support all PHP interfaces implements and classes extends
        $list = array('\\jsonserializable'		=> array('jsonserialize' => '\\mixed'),
                      '\\exception' 			=> array('__wakeup'		 => '\\void'),
                      '\\filteriterator' 		=> array('accept'		 => '\\bool'),
                      '\\countable' 			=> array('count'		 => '\\int'),
                      '\\php_user_filter' 		=> array('filter'		 => '\\int'),
                      '\\arrayaccess' 			=> array('offsetexists'  => '\\bool',
                                                         'offsetset' 	 => '\\void',
                                                         'offsetget' 	 => '\\mixed',
                                                         'offsetunset' 	 => '\\void',
                                                        ),
                      '\\iterator' 				=> array('next' 	     => '\\void',
                                                         'rewind' 	     => '\\void',
                                                         'valid' 	     => '\\bool',
                                                         'current' 	     => '\\mixed',
                                                         'key' 	 	     => '\\mixed',
                                                        ),
                      '\\sessionhandlerinterface' => array(	'destroy' 	 => '\\bool',
//					  										'gc' 	 	 => '\\key',				// int|false
                                                            'write' 	 	 => '\\bool',
                                                            'close' 	 	 => '\\bool',
//							  								'read' 	 	 => '\\key',				// string|false
                                                            'open' 	 	 => '\\bool',
                                                            ),
                      /*
                      // @todo : Multiple types are not supported yet
                      '\\recursiveiterator' 	=> array('getchildren'),		// ?RecursiveIterator
                      '\\iteratoraggregate' 	=> array('getiterator'),		// ?RecursiveIterator
                      '\\iteratoraggregate' 	=> array('getiterator'),		// ?RecursiveIterator
                      
                                                              */

        );

        foreach ($list as $class => $methods) {
            foreach ($methods as $name => $type) {
                $this->atomIs(self::STATIC_NAMES)
                     ->fullnspathIs($class)
                     ->inIs('IMPLEMENTS')
                     ->goToAllChildren(self::INCLUDE_SELF)

                     ->goToMethod($name)
                     ->not(
                         $this->side()
                              ->outIs('ATTRIBUTE')
                              ->fullnspathIs('\\returntypewillchange')
                     )
                     ->as('results')
                     ->not(
                         $this->side()
                              ->outIs('RETURNTYPE')
                              ->atomIs('Scalartypehint')
                              ->fullnspathIs($type)
                     )
                     ->back('results');
                $this->prepareQuery();
            }
        }
    }
}

?>
