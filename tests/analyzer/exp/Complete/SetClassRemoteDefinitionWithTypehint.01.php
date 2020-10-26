<?php

$expected     = array('$b->bar( )',
                      '$a->bar( )',
                      '$a->bar( )',
                     );

$expected_not = array('$c->cou( )',
                     );

$fetch_query = 'g.V().hasLabel("Method").out("DEFINITION").hasLabel("Methodcall").values("fullcode")';

?>