<?php

function foo() {
    $a = 1;
    $b = $a + 2;
    $c = $a + $b;
    
    return $c;
}

function bar() {
    $b = ($a = 1) + 2;
    $c = $a + $b;
    
    return $c;
}

?>