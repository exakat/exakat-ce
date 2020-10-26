<?php

$expected     = array('$a->pab2',
                      '$a->pab',
                      '$b->pab',
                     );

$expected_not = array('$b->cou',
                      '$b->pab2',
                     );

$fetch_query = 'g.V().hasLabel("Propertydefinition").out("DEFINITION").hasLabel("Member").values("fullcode")';

?>