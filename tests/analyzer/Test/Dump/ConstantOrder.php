<?php

namespace Test\Dump;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class ConstantOrder extends Analyzer {
    /* 1 methods */

    public function testDump_ConstantOrder01()  { $this->generic_test('Dump/ConstantOrder.01'); }
}
?>