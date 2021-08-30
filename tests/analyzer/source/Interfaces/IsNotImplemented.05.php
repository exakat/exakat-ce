<?php

interface i {
    function i1 () ;
    function i2 () ;
    function i3 () ;
}

class a {

}

abstract class a1 extends a implements i {
    function i1 () {}
}

// This is a fatal error, even when not used
class a2 extends a1{
    function i2 () {}
}

class a3 extends a2{
    function i3 () {}
}

class a3a extends a2{
    function i4 () {}
}

?>