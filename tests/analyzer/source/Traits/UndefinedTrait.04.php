<?php

use useTrait as useTrait2;
use const useConst as useConst2;

trait t {}

class x1 {
    use undefined;
    use t;
    use useTrait, useTrait2;
    use useConst, useConst2;
    use Stubs\stubTraits;
}

?>
