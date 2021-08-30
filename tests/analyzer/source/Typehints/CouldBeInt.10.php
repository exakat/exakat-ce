<?php

abstract class i {
    abstract function fooA(int $a);
    abstract function fooR();
    abstract function foo();
}

class a extends i {
    function fooA($a) {}
    function fooR() : int {}
    function foo() {}
}
