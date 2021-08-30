<?php

namespace Test\Classes;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class OverwrittenConst extends Analyzer {
    /* 2 methods */

    public function testClasses_OverwrittenConst01()  { $this->generic_test('Classes_OverwrittenConst.01'); }
    public function testClasses_OverwrittenConst02()  { $this->generic_test('Classes/OverwrittenConst.02'); }
}
?>