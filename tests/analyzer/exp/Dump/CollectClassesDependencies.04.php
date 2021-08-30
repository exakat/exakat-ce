<?php

$expected     = array(array (
  'including' => '\\y',
  'including_name' => 'y',
  'including_type' => 'class',
  'included' => 'new',
  'included_name' => '\\x',
  'included_type' => 'x',
  'type' => 'class',
),
                      array (
  'including' => '\\x',
  'including_name' => 'x',
  'including_type' => 'class',
  'included' => 'new',
  'included_name' => '\\y',
  'included_type' => 'y',
  'type' => 'class',
),
                     );

$expected_not = array('',
                      '',
                     );

?>