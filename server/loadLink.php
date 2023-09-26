<?php

include 'vendor/autoload.php';
include 'library/Exakat/Graph/Helpers/GraphsonV3.php';

use Brightzone\GremlinDriver\Connection;

		$path     = $argv[1];
        $host     = $argv[2]; 
        $port     = $argv[3]; 

        $query = <<<'GREMLIN'
new File(file).eachLine {
	link = it.split(",");
    g.V(link[1]).addE(link[0]).to( __.V(link[2])).iterate();
}
GREMLIN;

        $db = new Connection(array( 'host'     => $host,
                                    'port'     => $port,
                                    'graph'    => 'graph',
                                    'emptySet' => true,
                                   ) );
		$db->open();
		
        $db->message->bindValue('file', $path);

		$res = $db->send($query);
		unlink($path);
?>