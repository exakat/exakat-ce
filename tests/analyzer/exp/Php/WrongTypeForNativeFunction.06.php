<?php

$expected     = array('explode(\'/\', substr($a, $b1) ?? \'\')',
                      'explode(\'/\', shell_exec($a, $b2))',
                      'explode(\'/\', strpos($a, $b4))',
                      'explode(\'/\', parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?? \'\')',
                      'explode(\'/\', shell_exec($a, $b2) ?? \'\')',
                     );

$expected_not = array('unlink(__FILE__)',
                      'explode(\'/\', shell_exec($a, $b2) ?? \'\')',
                      'explode(\'/\', shell_exec($a, $b2) ?: \'\')',
                      'explode(\'/\', strpos($a, $b4) ?: \'\')',
                     );

?>