<?php

namespace Test\Structures;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class ArrayMapPassesByValue extends Analyzer {
    /* 1 methods */

    public function testStructures_ArrayMapPassesByValue01()  { $this->generic_test('Structures/ArrayMapPassesByValue.01'); }
}
?>