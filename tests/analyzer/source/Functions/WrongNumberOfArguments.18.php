<?php

class x {
    function __invoke($a) {}
    
    function foo() {
        $this();
        $this(1);
        $this(2, 3);
    }
}

?>