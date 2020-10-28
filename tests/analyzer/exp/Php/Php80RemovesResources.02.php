<?php

$expected     = array('is_resource($this->a)', 
                      'is_resource(self::$c)',
                     );

$expected_not = array('is_resource($this->b)', 
                     );

?>