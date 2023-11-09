<?php

$path = $argv[1]; 
$host = $argv[2]; 
$port = $argv[3]; 
$includePath = $argv[4];

include $includePath.'/vendor/autoload.php';

use Brightzone\GremlinDriver\Connection;


$db = new Connection(array( 'host'     => $host,
                            'port'     => $port,
                            'graph'    => 'graph',
                            'emptySet' => true,
                           ) );
$db->open();

$res = $db->send("graph.io(IoCore.graphson()).readGraph(\"$path\");");
unlink($path);

?>