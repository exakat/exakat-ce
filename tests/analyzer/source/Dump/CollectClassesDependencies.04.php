<?php

class y {
    function foo() {
        new x();
    }
}

class x {
    function foo() {
        new y();
    }
}
?>