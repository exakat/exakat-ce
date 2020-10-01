<?php

interface A {
    function bar();
}

class AB implements A {
    function bar() {}
}

class AB2 implements A {
    function bar() {}
}

function foo(A $a, AB $b) {
    $a->bar();

    $b->bar();
    $b->cou();
}


?>