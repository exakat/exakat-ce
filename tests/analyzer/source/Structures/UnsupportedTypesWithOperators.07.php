<?php

array($a => 1) + compact($b);
3 + compact($b);

array($a => 1) + (array) $c;
array($a => 1) + (int) $c;

?>