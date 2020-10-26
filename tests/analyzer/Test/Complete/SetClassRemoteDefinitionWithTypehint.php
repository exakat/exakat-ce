<?php

namespace Test\Complete;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class SetClassRemoteDefinitionWithTypehint extends Analyzer {
    /* 4 methods */

    public function testComplete_SetClassRemoteDefinitionWithTypehint01()  { $this->generic_test('Complete/SetClassRemoteDefinitionWithTypehint.01'); }
    public function testComplete_SetClassRemoteDefinitionWithTypehint02()  { $this->generic_test('Complete/SetClassRemoteDefinitionWithTypehint.02'); }
    public function testComplete_SetClassRemoteDefinitionWithTypehint03()  { $this->generic_test('Complete/SetClassRemoteDefinitionWithTypehint.03'); }
    public function testComplete_SetClassRemoteDefinitionWithTypehint04()  { $this->generic_test('Complete/SetClassRemoteDefinitionWithTypehint.04'); }
}
?>