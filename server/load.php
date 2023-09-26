<?php

include 'vendor/autoload.php';
include 'library/Exakat/Graph/Helpers/GraphsonV3.php';

use Brightzone\GremlinDriver\Connection;

        $path = $argv[1]; 
        $host = $argv[2]; 
        $port = $argv[3]; 

        $db = new Connection(array( 'host'     => $host,
                                    'port'     => $port,
                                    'graph'    => 'graph',
                                    'emptySet' => true,
                                   ) );
		$db->open();

		$res = $db->send("graph.io(IoCore.graphson()).readGraph(\"$path\");");
		unlink($path);

?>