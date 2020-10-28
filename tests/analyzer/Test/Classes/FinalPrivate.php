<?php

namespace Test\Classes;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class FinalPrivate extends Analyzer {
    /* 1 methods */

    public function testClasses_FinalPrivate01()  { $this->generic_test('Classes/FinalPrivate.01'); }
}
?>