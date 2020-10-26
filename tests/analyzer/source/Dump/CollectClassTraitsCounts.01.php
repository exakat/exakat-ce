<?php

class x0 {}

class x1 {
    use t1;
}

class x2a {
    use t1, t2;
}

class x2b {
    use t1;
    use t2;
}

class x3 {
    use t1, t3;
    use t2;
}

?>