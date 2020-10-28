<?php

$expected     = array('array_map(\'array_pop\', $a)',
                      'array_map(\'foo\', $a)',
                      'array_map(function (&$b) { /**/ } , $a)',
                      'array_map(fn (&$d) => 2, $a)',
                     );

$expected_not = array('array_map(\'strtolower\', $a)',
                      'array_map(\'foo2\', $a)',
                      'array_map(\'foo3\', $a)',
                      'array_map(function ($a) { /**/ } , $a)',
                      'array_map(fn ($c) => 2, $a)',
                     );

?>