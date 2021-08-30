<?php

$expected     = array('function foo4($d = 3, $a) { /**/ } ',
                     );

$expected_not = array('function foo3($c = 2, ...$a) { /**/ } ',
                      'function foo(...$a) { /**/ } ',
                      'function foo2($b, ...$a) { /**/ } ',
                     );

?>