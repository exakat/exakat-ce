<?php

$expected     = array('\\StdCLASS',
                      '\\Curlfile',
                     );

$expected_not = array('\\Stdclass',  // Not recognizedd as an interface
                      '\\ArrayAccess',
                     );

?>