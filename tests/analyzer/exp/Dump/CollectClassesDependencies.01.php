<?php

$expected     = array (
  0 => 
  array (
    'including' => '\\j',
    'including_name' => 'j',
    'including_type' => 'interface',
    'included' => 'extends',
    'included_name' => '\\i',
    'included_type' => 'i',
    'type' => 'interface',
  ),
  1 => 
  array (
    'including' => '\\y',
    'including_name' => 'y',
    'including_type' => 'class',
    'included' => 'implements',
    'included_name' => '\\i',
    'included_type' => 'i',
    'type' => 'interface',
  ),
  2 => 
  array (
    'including' => '\\y',
    'including_name' => 'y',
    'including_type' => 'class',
    'included' => 'extends',
    'included_name' => '\\x',
    'included_type' => 'x',
    'type' => 'class',
  ),
);

$expected_not = array('',
                      '',
                     );

?>