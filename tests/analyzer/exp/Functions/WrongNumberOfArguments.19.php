<?php

$expected     = array('foo(...[1, 2, 3])',
                      'foo(...[1])',
                      'foo(...[ ])',
                      'x(...[1, 2, 3])',
                      'x(...[1])',
                      'x(...[ ])',
                      'foo(...[1, \'a\' => 2, 3])',
                      'x(...[1, \'a\' => 2, 3])',
                     );

$expected_not = array('',
                      '',
                     );

?>