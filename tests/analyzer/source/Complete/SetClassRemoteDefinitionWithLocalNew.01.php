<?php

trait t {
    function fooT() {}
}

class ba extends A {
    function fooB() {}
}

class a {
    use T; 
    function fooA() {}
}

function foo() {
    $a = new A();
    
    $a->fooA();
    $a->fooB();
    $a->fooT();
    $a->fooC();
}

?>