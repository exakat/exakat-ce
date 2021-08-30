<?php

namespace Test\Complete;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class FollowClosureDefinition extends Analyzer {
    /* 3 methods */

    public function testComplete_FollowClosureDefinition01()  { $this->generic_test('Complete/FollowClosureDefinition.01'); }
    public function testComplete_FollowClosureDefinition02()  { $this->generic_test('Complete/FollowClosureDefinition.02'); }
    public function testComplete_FollowClosureDefinition03()  { $this->generic_test('Complete/FollowClosureDefinition.03'); }
}
?>