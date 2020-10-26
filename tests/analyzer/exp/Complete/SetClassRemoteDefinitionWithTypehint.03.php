<?php

$expected     = array('$a::X', 
                      'X::X', 
                      '$a::C',
                     );

$expected_not = array('$b::C',
                      '$c::X',
                     );

$fetch_query = 'g.V().hasLabel("Constant").out("DEFINITION").hasLabel("Staticconstant").values("fullcode")';

?>