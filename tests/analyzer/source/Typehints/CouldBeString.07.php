<?php

abstract class i {
    abstract function fooA(string $a);
    abstract function fooR();
    abstract function foo();
}

class a extends i {
    function fooA($a) {}
    function fooR() : string {}
    function foo() {}
}
