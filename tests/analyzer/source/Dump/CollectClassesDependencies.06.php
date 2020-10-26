<?php

class a {}
class b {}
class c {}

class x extends y {
    function foo() {
        a::$p;
        b::m();
        c::Y;
        self::D;
        parent::Y;
        $this::Yes;
    }
}
?>