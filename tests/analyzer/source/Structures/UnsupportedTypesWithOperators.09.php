<?php

$a = array();

if ( isset( $a['b'] ) ) {
	$a = array(1 ) + $a;
}
if ( isset( $a['c'] ) ) {
	$a = array( 2) + $a;
}

$b = 1;

if ( $b === 1) {
	$b = array( 3) + $b;
}


?>