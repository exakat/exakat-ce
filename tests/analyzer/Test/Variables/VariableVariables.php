<?php

namespace Test\Variables;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class VariableVariables extends Analyzer {
    /* 2 methods */

    public function testVariables_VariableVariables01()  { $this->generic_test('Variables_VariableVariables.01'); }
    public function testVariables_VariableVariables02()  { $this->generic_test('Variables/VariableVariables.02'); }
}
?>