<?php

function foo(?int $x) { echo $x[1]; }
function foo2(?A $x) { echo $x[2]; }
function foo3(?A $x) { echo $x->b; }

?>