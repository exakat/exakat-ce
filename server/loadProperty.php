<?php

if (file_exists('vendor/autoload.php')){
	include 'vendor/autoload.php';
} else {
	include 'phar://exakat.phar/vendor/autoload.php';
}

use Brightzone\GremlinDriver\Connection;

$property = $argv[1];
$path     = $argv[2];
$host     = $argv[3]; 
$port     = $argv[4]; 

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

$res = $db->send($query);
unlink($path);

?>