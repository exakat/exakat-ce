<?php

$a = $b - $c + $c;

$a5 = 2 + $b[3] - $c5 + $d->foo(1,2,3) + $c5 + $b[3];
$a6 = 2 + $b[4] - $c5 + $d->foo(1,2,3) + $c5 - $b[4];
$a7 = 2 + $b[5] + $c5 + $d->foo(1,2,3) + $b[5] - $c5;
$a8 = 2 + $b[6] + $c5 + $d->foo(1,2,3) - $b[6] - $c5;


?>