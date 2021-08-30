<?php

interface i {
    function fooA(float $a);
    function fooR();
    function foo();
}

class a implements i {
    function fooA($a) {}
    function fooR() : float {}
    function foo() {}
}
