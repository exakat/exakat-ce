<?php

$expected     = array('implode(AR, $theTable)',
                      'implode(\', \', $returnS)',
                      'implode($d, $returnStatic2)',
                     );

$expected_not = array('implode($theTable, AR)',
                      'implode(\', \', $return)',
                      'implode(\', \', $returnStatic)',
                     );

?>