<?php 

class x1 {
    public static $defined1 = 1;
}

class x2 extends x1 {
    public static $defined2 = 1;
}

class x3 extends x2 {
    public static $defined3 = 1;

    function y() {
        self::$defined1 = 1;
        self::$defined2 = 1;
        self::$defined3 = 1;
        self::$undefined = 1;
    }
}

?>