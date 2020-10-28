<?php

class x {
    private $a = null;
    private static $c = null;
    private $b  = 2;
    
    function __construct() {
        $this->a = curl_init();
        $this->b = fopen();
        self::$c = socket_addrinfo_connect();
    }

    function foo() {
        is_resource($this->a);
        is_resource($this->b);
        is_resource(self::$c);
    }
}

?>