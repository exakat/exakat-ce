<?php

$expected     = array(array (
    'name' => 'global',
    'type' => 'File',
    'tokens' => 0,
    'expressions' => 0,
    'file' => './tests/analyzer/source/Dump/CollectReadability.01.php',
  ),
  1 => 
  array (
    'name' => 'foo',
    'type' => 'Function',
    'tokens' => 15,
    'expressions' => 4,
    'file' => './tests/analyzer/source/Dump/CollectReadability.01.php',
  ),
  2 => 
  array (
    'name' => 'bar',
    'type' => 'Function',
    'tokens' => 15,
    'expressions' => 3,
    'file' => './tests/analyzer/source/Dump/CollectReadability.01.php',
  ),

                     );

$expected_not = array('',
                      '',
                     );

?>