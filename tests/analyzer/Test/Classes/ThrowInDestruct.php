<?php

namespace Test\Classes;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class ThrowInDestruct extends Analyzer {
    /* 1 methods */

    public function testClasses_ThrowInDestruct01()  { $this->generic_test('Classes/ThrowInDestruct.01'); }
}
?>