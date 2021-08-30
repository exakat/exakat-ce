<?php

$expected     = array('$a',
                      '$b',
                      '$g',
                      '$f',
                      '$e',
                      '$d',
                      '$c',
                      '$GLOBALS',
                      '$GLOBALS[\'G\']',
                      '$GLOBALS[\'H\'][\'I\']',
                      '$GLOBALS[\'J\'][\'I\'][\'K\']',
                     );

$expected_not = array('$z',
                      '$GLOBALS[\'J\']',
                      '$GLOBALS[\'J\'][\'I\']',
                      '$GLOBALS[\'H\']',
                     );

?>