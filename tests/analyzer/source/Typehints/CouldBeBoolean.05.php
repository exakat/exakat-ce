<?php

abstract class i {
    abstract function fooA(bool $a);
    abstract function fooR();
    abstract function foo();
}

class a extends i {
    function fooA($a) {}
    function fooR() : bool {}
    function foo() {}
}
