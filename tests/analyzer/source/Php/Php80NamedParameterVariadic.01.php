<?php

function foo(...$b) {
    bar($b[0]);
}

function foo2(...$b) {
    $b = array_values($b);
    bar($b[0]);
}

function foo3(...$b) {
    bar($b['b']);
}


?>