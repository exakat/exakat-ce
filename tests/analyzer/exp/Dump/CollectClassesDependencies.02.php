<?php

$expected     = array (
  0 => 
  array (
    'including' => '\\x',
    'including_name' => 'x',
    'including_type' => 'class',
    'included' => 'typehint',
    'included_name' => '\\y',
    'included_type' => 'y',
    'type' => 'class',
  ),
  1 => 
  array (
    'including' => '\\x',
    'including_name' => 'x',
    'including_type' => 'class',
    'included' => 'typehint',
    'included_name' => '\\i',
    'included_type' => 'i',
    'type' => 'interface',
  ),
  2 => 
  array (
    'including' => '\\x',
    'including_name' => 'x',
    'including_type' => 'class',
    'included' => 'typehint',
    'included_name' => '\\z',
    'included_type' => 'z',
    'type' => 'class',
  ),
  3 => 
  array (
    'including' => '\\x',
    'including_name' => 'x',
    'including_type' => 'class',
    'included' => 'typehint',
    'included_name' => '\\i2',
    'included_type' => 'i2',
    'type' => 'interface',
  ),

);

$expected_not = array('',
                      '',
                     );

?>