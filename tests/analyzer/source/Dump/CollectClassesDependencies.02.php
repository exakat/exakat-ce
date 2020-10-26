<?php

class z {}
class y {}
interface i {}
interface i2 {}

class x {
    function foo(Y $y, string $s, i $i) {}
    function bar() : z { return $a; }
    function bar2() : i2 { return $a; }
    function barbar() : ?string { return $b; }
}
?>