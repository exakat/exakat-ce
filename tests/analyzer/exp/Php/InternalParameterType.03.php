<?php

$expected     = array('preg_match(3, $typehints)',
                      'preg_match(\'/\\bnull\\b/i\', $typehints, "")',
                      'preg_match(\'/\\bnull\\b/i\', $typehints, $r, "")',
                     );

$expected_not = array('preg_match(\'/\\bnull\\b/i\', $typehints)',
                      'preg_match(\'/\\bnull\\b/i\', $typehints, $r, 3)',
                      'preg_match(\'/\\bnull\\b/i\', $typehints, [])',
                     );

?>