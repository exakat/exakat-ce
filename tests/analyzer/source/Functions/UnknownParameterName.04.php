<?php

bar(...['foo' => 123, 'bar' => 234, 'baz' => 345]);
bar(...['foo' => 123, 'bar' => 234]);

function bar($foo, $bar, $bac) {}

?>