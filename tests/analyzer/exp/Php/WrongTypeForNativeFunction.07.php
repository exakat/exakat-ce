<?php

$expected     = array('log((object) -1.2)',
                      'log((string) "3")',
                      'log((array) "3")',
                     );

$expected_not = array('log((int) "3")',
                      'log((int) 4)',
                      'log((double) 5)',
                     );

?>