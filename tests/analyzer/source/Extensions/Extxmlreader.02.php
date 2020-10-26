<?php

xmlreader::NONE;
function foo(xmlreader $a) {
    $a->localName;
    $a->localName2;
    
    $a->close();
    $a->close2();

}

xmlreader::close();
xmlreader::$localName;



?>