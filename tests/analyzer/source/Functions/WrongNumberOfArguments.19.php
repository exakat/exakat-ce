<?php

function foo($a, $b) {}

foo(...[]);
foo(...[1]);
foo(...[1, 2]);
foo(...[1, 2, 3]);
foo(...[1, 'a' => 2, 3]);

class x {
    function __construct($a, $b) {}
}

new x(...[]);
new x(...[1]);
new x(...[1, 2]);
new x(...[1, 2, 3]);
new x(...[1, 'a' => 2, 3]);

?>