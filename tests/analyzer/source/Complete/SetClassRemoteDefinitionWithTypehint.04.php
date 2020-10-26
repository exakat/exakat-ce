<?php

function foo(X $a, Y $b, $c) {
    echo $a::C;
    echo $a::X;
    echo X::X;
    
    echo $b::C;
    echo $c::X;
}

class x { const X = 1;}

class xa extends x { const C = 1;}
class xb extends x { const C = 2;}

?>