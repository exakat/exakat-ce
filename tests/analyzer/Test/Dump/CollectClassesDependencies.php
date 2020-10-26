<?php

namespace Test\Dump;

use Test\Analyzer;

include_once dirname(__DIR__, 2).'/Test/Analyzer.php';

class CollectClassesDependencies extends Analyzer {
    /* 6 methods */

    public function testDump_CollectClassesDependencies01()  { $this->generic_test('Dump/CollectClassesDependencies.01'); }
    public function testDump_CollectClassesDependencies02()  { $this->generic_test('Dump/CollectClassesDependencies.02'); }
    public function testDump_CollectClassesDependencies03()  { $this->generic_test('Dump/CollectClassesDependencies.03'); }
    public function testDump_CollectClassesDependencies04()  { $this->generic_test('Dump/CollectClassesDependencies.04'); }
    public function testDump_CollectClassesDependencies05()  { $this->generic_test('Dump/CollectClassesDependencies.05'); }
    public function testDump_CollectClassesDependencies06()  { $this->generic_test('Dump/CollectClassesDependencies.06'); }
}
?>