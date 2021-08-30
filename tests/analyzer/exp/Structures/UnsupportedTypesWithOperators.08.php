<?php

$expected     = array('array(3 => 4) + $i',
                      '$a + $i',
                     );

$expected_not = array('$a + CONSTANT',
                      '!aaaa($tbd)',
                      '!d($tbd)',
                     );

?>