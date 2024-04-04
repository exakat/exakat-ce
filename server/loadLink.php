<?php

use Brightzone\GremlinDriver\Connection;

$path        = $argv[1];
$host        = $argv[2]; 
$port        = $argv[3]; 
$includePath = $argv[4];
$logFile	 = $argv[5];

include $includePath.'/vendor/autoload.php';

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

foreach(range(0, 5) as $try) {
	$res = send($db, $query, $try, $logFile, $path);
	if ($res === 0) {
		break;
	}
}
unlink($path);

function send($db, $query, $try = 0, $logFile = '', string $path = '') : int {
	try {
		$db->send($query);

		if ($try > 0) {
			file_put_contents($logFile, "loadLink.php $path $try OK\n========\n", FILE_APPEND);
		}
		$error = 0;
	} catch (\Throwable $t) {
		file_put_contents($logFile, "loadLink.php $path $try\n".$t->getMessage()."========\n", FILE_APPEND);
		sleep(rand(1, 10));
		$error = $try + 1;
	}
	
	return $error;
}

?>