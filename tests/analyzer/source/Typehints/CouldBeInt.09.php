<?php

interface i {
    function fooA(int $a);
    function fooR();
    function foo();
}

class a implements i {
    function fooA($a) {}
    function fooR() : int {}
    function foo() {}
}
