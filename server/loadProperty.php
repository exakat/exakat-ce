<?php

use Brightzone\GremlinDriver\Connection;

$property    = $argv[1];
$path        = $argv[2];
$host        = $argv[3]; 
$port        = $argv[4]; 
$includePath = $argv[5];
$logFile	 = $argv[6];

include $includePath.'/vendor/autoload.php';

$query = <<<'GREMLIN'
new File(path).eachLine {
    vertices = it.split(',');
    g.V(vertices).property(property, true).iterate();
}
GREMLIN;

$db = new Connection(array( 'host'     => $host,
                             'port'     => $port,
                             'graph'    => 'graph',
                             'emptySet' => true,
                            ) );
$db->open();
$db->message->bindValue('property', $property);
$db->message->bindValue('path', $path);

foreach(range(0, 5) as $try) {
	$res = send($db, $query, $try);
	if ($res === 0) {
		break;
	}
}
unlink($path);

function send($db, $query, $try = 0, $logFile = '', $path = '') : int {
	try {
		$db->send($query);

		if ($try > 0) {
			file_put_contents($logFile, "loadProperty.php $path $try OK\n========\n", FILE_APPEND);
		}
		$error = 0;
	} catch (\Throwable $t) {
		file_put_contents($logFile, "loadProperty.php $path $try\n".$t->getMessage()."========\n", FILE_APPEND);
		sleep(rand(1, 10));
		$error = $try + 1;
	}
	
	return $error;
}

?>