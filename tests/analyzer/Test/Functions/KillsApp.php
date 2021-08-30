<?php

namespace Test\Functions;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class KillsApp extends Analyzer {
    /* 4 methods */

    public function testFunctions_KillsApp01()  { $this->generic_test('Functions_KillsApp.01'); }
    public function testFunctions_KillsApp02()  { $this->generic_test('Functions_KillsApp.02'); }
    public function testFunctions_KillsApp03()  { $this->generic_test('Functions/KillsApp.03'); }
    public function testFunctions_KillsApp04()  { $this->generic_test('Functions/KillsApp.04'); }
}
?>