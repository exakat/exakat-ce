<?php

$expected     = array('$this->undefined',
                      'x::$y',
                     );

$expected_not = array('$this->defined',
                      '$this->undefinedButMagic',
                      '$y->undefinedButNotInternal',
                     );

?>