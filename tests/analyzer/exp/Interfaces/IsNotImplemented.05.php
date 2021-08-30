<?php

$expected     = array('class a2 extends a1 { /**/ } ',
                      'class a3a extends a2 { /**/ } ',
                     );

$expected_not = array('class a { /**/ } ',
                      'class a3 extends a2 { /**/ } ',
                      'interface i { /**/ } ',
                      'class a1 extends a implements i { /**/ } ',
                     );

?>