<?php

namespace F {
class A extends E implements I1,I2 {
    use T;
}

trait T {}

interface I1{}
interface I2{}
class E {}
}

namespace G {

use T as ttt;
use G\T as ttt2;
use F\t as TT;

class A extends E implements I1,I2 {
    use T;
    use TT;
    use ttt, ttt2;
    use undefined;
}

trait T {}

interface I1{}
interface I2{}
class E {}
}

?>
