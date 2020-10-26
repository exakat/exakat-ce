<?php

$expected     = array('$a::X', 
                      'X::X', 
                     );

$expected_not = array('$b::C',
                      '$c::X',
                      '$a::C',
                     );

$fetch_query = 'g.V().hasLabel("Constant").out("DEFINITION").hasLabel("Staticconstant").values("fullcode")';

?>