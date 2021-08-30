<?php

$expected     = array('function ($a) { /**/ } ',
                      '$a',
                      '$b',
                      '$c',
                      'fn ($c) => strval($c)',
                     );

$expected_not = array('function foo($d) { /**/ } ',
                     );

?>