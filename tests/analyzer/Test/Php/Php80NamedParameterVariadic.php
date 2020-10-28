<?php

namespace Test\Php;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class Php80NamedParameterVariadic extends Analyzer {
    /* 1 methods */

    public function testPhp_Php80NamedParameterVariadic01()  { $this->generic_test('Php/Php80NamedParameterVariadic.01'); }
}
?>