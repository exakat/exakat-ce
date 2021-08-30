<?php

abstract class i {
    abstract function fooA(array $a);
    abstract function fooR();
    abstract function foo();
}

class a extends i {
    function fooA($a) {}
    function fooR() : array {}
    function foo() {}
}
