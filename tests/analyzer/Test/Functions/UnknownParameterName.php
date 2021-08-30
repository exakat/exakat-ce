<?php

namespace Test\Functions;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class UnknownParameterName extends Analyzer {
    /* 4 methods */

    public function testFunctions_UnknownParameterName01()  { $this->generic_test('Functions/UnknownParameterName.01'); }
    public function testFunctions_UnknownParameterName02()  { $this->generic_test('Functions/UnknownParameterName.02'); }
    public function testFunctions_UnknownParameterName03()  { $this->generic_test('Functions/UnknownParameterName.03'); }
    public function testFunctions_UnknownParameterName04()  { $this->generic_test('Functions/UnknownParameterName.04'); }
}
?>