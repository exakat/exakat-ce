<?php

$expected     = array('dirname(3)',
                      'dirname($d = 3)',
                     );

$expected_not = array('dirname($d = \'string\')',
                      'dirname((\'a\' . 3))',
                      'dirname(C::class)',
                      'memory_get_usage( )',
                     );

?>