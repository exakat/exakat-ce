<?php

$expected     = array('~fooInt( )',
                      '!fooArray( )',
                      '~fooArray( )',
                     );

$expected_not = array('~foo( )',
                      '!fooInt( )',
                      '!foo( )',
                     );

?>