<?php

$expected     = array('$b .= chr(($this->x[$i] & 0x000000FF)) . chr(($this->x[$i] & 0x0000FF00) >> 8) . chr(($this->x[$i] & 0x00FF0000) >> 16)',
                     );

$expected_not = array('$b .= chr(($this->x[$i] & 0x000000FF))',
                     );

?>