<?php

$expected     = array('$a->localName',
                      '$a->close( )',
                      'xmlreader::NONE',
                      'xmlreader',
                      'xmlreader',
                      'xmlreader',
                      'xmlreader',
                     );

$expected_not = array('xmlreader::$localName2',
                      'xmlreader::close2( )',
                      'xmlreader::NONE2',
                     );

?>