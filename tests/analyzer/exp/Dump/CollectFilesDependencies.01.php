<?php

$expected     = array(array (
  'including' => '/main.php',
  'included' => '\'include.php\'',
  'type' => 'include',
),
                      array (
  'including' => '/main.php',
  'included' => '\'include.php\'',
  'type' => 'include',
),
                      array (
  'including' => '/main.php',
  'included' => '\'include.php\'',
  'type' => 'include',
),
                      array (
  'including' => '/main.php',
  'included' => '/B.php',
  'type' => 'use',
),
                      array (
  'including' => '/main.php',
  'included' => '/C.php',
  'type' => 'use',
),
                      array (
  'including' => '/main.php',
  'included' => '/A.php',
  'type' => 'use',
),
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