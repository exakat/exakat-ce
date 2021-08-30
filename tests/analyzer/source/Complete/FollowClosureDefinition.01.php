<?php

function foo($a) { 
    $a();
}


foo(function($b) {});
foo(fn ($c) => 3);
foo('D');

$d = function($D) {};
foo($d);
$e = fn ($c) => 4;
foo($e);
$f = fn ($c) => 54;

?>