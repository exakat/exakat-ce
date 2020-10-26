<?php

trait t3 {
    use t3;
}

trait t2 {
    use t3;
}

trait t1 {
    use t3;
}

class x {
    use t1, t2;
}
?>