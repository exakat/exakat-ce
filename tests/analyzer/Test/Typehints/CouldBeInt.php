<?php

namespace Test\Typehints;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class CouldBeInt extends Analyzer {
    /* 10 methods */

    public function testTypehints_CouldBeInt01()  { $this->generic_test('Typehints/CouldBeInt.01'); }
    public function testTypehints_CouldBeInt02()  { $this->generic_test('Typehints/CouldBeInt.02'); }
    public function testTypehints_CouldBeInt03()  { $this->generic_test('Typehints/CouldBeInt.03'); }
    public function testTypehints_CouldBeInt04()  { $this->generic_test('Typehints/CouldBeInt.04'); }
    public function testTypehints_CouldBeInt05()  { $this->generic_test('Typehints/CouldBeInt.05'); }
    public function testTypehints_CouldBeInt06()  { $this->generic_test('Typehints/CouldBeInt.06'); }
    public function testTypehints_CouldBeInt07()  { $this->generic_test('Typehints/CouldBeInt.07'); }
    public function testTypehints_CouldBeInt08()  { $this->generic_test('Typehints/CouldBeInt.08'); }
    public function testTypehints_CouldBeInt09()  { $this->generic_test('Typehints/CouldBeInt.09'); }
    public function testTypehints_CouldBeInt10()  { $this->generic_test('Typehints/CouldBeInt.10'); }
}
?>