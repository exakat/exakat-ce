<?php

$expected     = array (
  0 => 
  array (
    'including' => '\\x',
    'including_name' => 'x',
    'including_type' => 'class',
    'included' => 'staticproperty',
    'included_name' => '\\a',
    'included_type' => 'a',
    'type' => 'class',
  ),
  1 => 
  array (
    'including' => '\\x',
    'including_name' => 'x',
    'including_type' => 'class',
    'included' => 'staticmethodcall',
    'included_name' => '\\b',
    'included_type' => 'b',
    'type' => 'class',
  ),
  2 => 
  array (
    'including' => '\\x',
    'including_name' => 'x',
    'including_type' => 'class',
    'included' => 'staticconstant',
    'included_name' => '\\c',
    'included_type' => 'c',
    'type' => 'class',
  ),
  3 => 
  array (
    'including' => '\\x',
    'including_name' => 'x',
    'including_type' => 'class',
    'included' => 'staticconstant',
    'included_name' => '\\x',
    'included_type' => 'x',
    'type' => 'class',
  ),
  4 => 
  array (
    'including' => '\\x',
    'including_name' => 'x',
    'including_type' => 'class',
    'included' => 'staticconstant',
    'included_name' => '\\x',
    'included_type' => 'x',
    'type' => 'class',
  ),
);

$expected_not = array('',
                      '',
                     );

?>