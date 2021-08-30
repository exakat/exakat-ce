<?php

namespace Test\Typehints;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class CouldBeArray extends Analyzer {
    /* 5 methods */

    public function testTypehints_CouldBeArray01()  { $this->generic_test('Typehints/CouldBeArray.01'); }
    public function testTypehints_CouldBeArray02()  { $this->generic_test('Typehints/CouldBeArray.02'); }
    public function testTypehints_CouldBeArray03()  { $this->generic_test('Typehints/CouldBeArray.03'); }
    public function testTypehints_CouldBeArray04()  { $this->generic_test('Typehints/CouldBeArray.04'); }
    public function testTypehints_CouldBeArray05()  { $this->generic_test('Typehints/CouldBeArray.05'); }
}
?>