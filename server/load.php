<?php

use Brightzone\GremlinDriver\Connection;

$path        = $argv[1]; 
$host        = $argv[2]; 
$port        = $argv[3]; 
$includePath = $argv[4];
$logFile     = $argv[5];

include $includePath.'/vendor/autoload.php';

$db = new Connection(array( 'host'     => $host,
                            'port'     => $port,
                            'graph'    => 'graph',
                            'emptySet' => true,
                           ) );
$db->open();

try {
	$res = $db->send("graph.io(IoCore.graphson()).readGraph(\"$path\");");
} catch (\Throwable $t) {
	file_put_contents($logFile, "load.php $path\n".$t->getMessage()."========\n", FILE_APPEND);
}

unlink($path);

?>