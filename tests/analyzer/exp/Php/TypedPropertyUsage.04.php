<?php

$expected     = array( 'private X|T $a', 
                       'public string $d = \'s\'', 
                       'var Z|A $z',
                     );

$expected_not = array('protected $c',
                     );
                     
$phpVersionRange = '8.1+';

?>