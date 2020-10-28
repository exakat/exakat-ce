<?php

array_map('foo', $a);
function foo(&$arg) {}

array_map('foo2', $a);
function foo2($arg) {}

array_map('foo3', $a);
function foo3($arg, &$arg2) {}

array_map('strtolower', $a);
array_map('array_pop', $a);

array_map(function ($a) {}, $a);
array_map(function (&$b) {}, $a);

array_map(fn ($c) => 1, $a);
array_map(fn (&$d) => 2, $a);

?>