<?php

class x {
    function foo() {
        $this->foo();
    }
    
    // calls a different method
    function bar() {
        $this->foo();
    }

}

?>