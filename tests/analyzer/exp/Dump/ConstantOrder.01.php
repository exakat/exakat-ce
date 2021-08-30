<?php

$expected     = array(array (
  'built' => '\\B',
  'built_fullcode' => 'B',
  'building' => '\\A',
  'building_fullcode' => 'A',
),
                      array (
  'built' => '\\C',
  'built_fullcode' => 'C',
  'building' => '\\i::I1',
  'building_fullcode' => 'i::I1',
),
                      array (
  'built' => '\\C',
  'built_fullcode' => 'C',
  'building' => '\\c::C2',
  'building_fullcode' => 'c::C2',
),
                      array (
  'built' => '\\C',
  'built_fullcode' => 'C',
  'building' => '\\A',
  'building_fullcode' => 'F',
),
                      array (
  'built' => '\\C',
  'built_fullcode' => 'C',
  'building' => '\\D',
  'building_fullcode' => '\\D',
),
                      array (
  'built' => '\\C2',
  'built_fullcode' => 'C2',
  'building' => '\\c::C1',
  'building_fullcode' => 'self::C1',
),
                     );

$expected_not = array(array (
  'built' => '\\B',
  'built_fullcode' => 'B',
  'building' => '\\B',
  'building_fullcode' => 'B',
),
                     );

?>