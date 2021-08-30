<?php

$expected     = array(array (
  'including' => '\\x',
  'including_name' => 'x',
  'including_type' => 'class',
  'included' => 'staticproperty',
  'included_name' => '\\a',
  'included_type' => 'a',
  'type' => 'class',
),
                      array (
  'including' => '\\x',
  'including_name' => 'x',
  'including_type' => 'class',
  'included' => 'staticmethodcall',
  'included_name' => '\\b',
  'included_type' => 'b',
  'type' => 'class',
),
                      array (
  'including' => '\\x',
  'including_name' => 'x',
  'including_type' => 'class',
  'included' => 'staticconstant',
  'included_name' => '\\c',
  'included_type' => 'c',
  'type' => 'class',
),
                      array (
  'including' => '\\x',
  'including_name' => 'x',
  'including_type' => 'class',
  'included' => 'staticconstant',
  'included_name' => '\\x',
  'included_type' => 'x',
  'type' => 'class',
),
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