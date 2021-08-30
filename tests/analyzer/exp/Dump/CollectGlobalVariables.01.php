<?php

$expected     = array(array (
  'variable' => './tests/analyzer/source/Dump/CollectGlobalVariables.01.php',
  'file' => 3,
  'line' => '$implicit_global',
  'isRead' => '',
  'isModified' => 1,
  'type' => 'implicit',
),
                      array (
  'variable' => './tests/analyzer/source/Dump/CollectGlobalVariables.01.php',
  'file' => 8,
  'line' => '$explicit_global',
  'isRead' => '',
  'isModified' => '',
  'type' => 'global',
),
                      array (
  'variable' => './tests/analyzer/source/Dump/CollectGlobalVariables.01.php',
  'file' => 5,
  'line' => '$explicit_global',
  'isRead' => '',
  'isModified' => '',
  'type' => 'global',
),
                      array (
  'variable' => './tests/analyzer/source/Dump/CollectGlobalVariables.01.php',
  'file' => 8,
  'line' => '$explicit_global_in_foo',
  'isRead' => '',
  'isModified' => '',
  'type' => 'global',
),
                     );

$expected_not = array('',
                      '',
                     );

?>