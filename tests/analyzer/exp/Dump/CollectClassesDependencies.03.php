<?php

$expected     = array(array (
  'including' => '\\t3',
  'including_name' => 't3',
  'including_type' => 'trait',
  'included' => 'use',
  'included_name' => '\\t3',
  'included_type' => 't3',
  'type' => 'trait',
),
                      array (
  'including' => '\\t2',
  'including_name' => 't2',
  'including_type' => 'trait',
  'included' => 'use',
  'included_name' => '\\t3',
  'included_type' => 't3',
  'type' => 'trait',
),
                      array (
  'including' => '\\t1',
  'including_name' => 't1',
  'including_type' => 'trait',
  'included' => 'use',
  'included_name' => '\\t3',
  'included_type' => 't3',
  'type' => 'trait',
),
                      array (
  'including' => '\\x',
  'including_name' => 'x',
  'including_type' => 'class',
  'included' => 'use',
  'included_name' => '\\t2',
  'included_type' => 't2',
  'type' => 'trait',
),
                      array (
  'including' => '\\x',
  'including_name' => 'x',
  'including_type' => 'class',
  'included' => 'use',
  'included_name' => '\\t1',
  'included_type' => 't1',
  'type' => 'trait',
),
                     );

$expected_not = array('',
                      '',
                     );

?>