<?php

namespace Test\Interfaces;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class Interfacenames extends Analyzer {
    /* 1 methods */

    public function testInterfaces_Interfacenames01()  { $this->generic_test('Interfaces_Interfacenames.01'); }
}
?>