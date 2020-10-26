<?php

$expected     = array('${$m[4]}', 
                      '${$c . \'_v\'}', 
                      '${$m->method( )}', 
                      '${$v}',
                     );

$expected_not = array('${liste}',
                      '${\'input\'};',
                     );

?>