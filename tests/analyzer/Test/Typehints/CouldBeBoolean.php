<?php

namespace Test\Typehints;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class CouldBeBoolean extends Analyzer {
    /* 5 methods */

    public function testTypehints_CouldBeBoolean01()  { $this->generic_test('Typehints/CouldBeBoolean.01'); }
    public function testTypehints_CouldBeBoolean02()  { $this->generic_test('Typehints/CouldBeBoolean.02'); }
    public function testTypehints_CouldBeBoolean03()  { $this->generic_test('Typehints/CouldBeBoolean.03'); }
    public function testTypehints_CouldBeBoolean04()  { $this->generic_test('Typehints/CouldBeBoolean.04'); }
    public function testTypehints_CouldBeBoolean05()  { $this->generic_test('Typehints/CouldBeBoolean.05'); }
}
?>