<?php

$expected     = array('@$noScream',
                      '@opendir(\'.\')',
                      '@$a->method( )',
                      '@$a->p',
                      '@C::D',
                     );

$expected_not = array('@',
                      '@fopen($a, \'r\')',
                      '@\\fopen($a, \'r\')',
                     );

?>