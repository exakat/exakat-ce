<?php

$expected     = array('$d *= 1',
                     );

$expected_not = array('$a *= $i',
                      '$b *= $foo',
                      '$c *= 0',
                     );

?>