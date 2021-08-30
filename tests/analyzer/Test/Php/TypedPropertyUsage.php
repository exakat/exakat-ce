<?php

namespace Test\Php;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class TypedPropertyUsage extends Analyzer {
    /* 4 methods */

    public function testPhp_TypedPropertyUsage01()  { $this->generic_test('Php/TypedPropertyUsage.01'); }
    public function testPhp_TypedPropertyUsage02()  { $this->generic_test('Php/TypedPropertyUsage.02'); }
    public function testPhp_TypedPropertyUsage03()  { $this->generic_test('Php/TypedPropertyUsage.03'); }
    public function testPhp_TypedPropertyUsage04()  { $this->generic_test('Php/TypedPropertyUsage.04'); }
}
?>