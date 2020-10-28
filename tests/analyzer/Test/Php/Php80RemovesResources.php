<?php

namespace Test\Php;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class Php80RemovesResources extends Analyzer {
    /* 2 methods */

    public function testPhp_Php80RemovesResources01()  { $this->generic_test('Php/Php80RemovesResources.01'); }
    public function testPhp_Php80RemovesResources02()  { $this->generic_test('Php/Php80RemovesResources.02'); }
}
?>