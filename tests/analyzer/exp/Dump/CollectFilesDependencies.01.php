<?php

$expected     = array (
  0 => 
  array (
    'including' => '/main.php',
    'included' => '\'include.php\'',
    'type' => 'include',
  ),
  1 => 
  array (
    'including' => '/main.php',
    'included' => '\'include.php\'',
    'type' => 'include',
  ),
  2 => 
  array (
    'including' => '/main.php',
    'included' => '\'include.php\'',
    'type' => 'include',
  ),
  3 => 
  array (
    'including' => '/main.php',
    'included' => '/B.php',
    'type' => 'use',
  ),
  4 => 
  array (
    'including' => '/main.php',
    'included' => '/C.php',
    'type' => 'use',
  ),
  5 => 
  array (
    'including' => '/main.php',
    'included' => '/A.php',
    'type' => 'use',
  ),
  6 => 
  array (
    'including' => '/main.php',
    'included' => '/D.php',
    'type' => 'use',
  ),
);

$expected_not = array('',
                      '',
                     );

?>