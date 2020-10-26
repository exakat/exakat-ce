<?php

$expected     = array('$c += 0',
                     );

$expected_not = array('$a += $i',
                      '$b += $foo',
                      '$d += 1',
                     );

?>