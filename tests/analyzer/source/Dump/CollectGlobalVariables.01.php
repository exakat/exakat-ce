<?php

$implicit_global = 1;

global $explicit_global;

function foo() {
    global $explicit_global, $explicit_global_in_foo;
    
    echo $GLOBALS['GLOBALS'];
}
?>