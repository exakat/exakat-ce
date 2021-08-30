<?php

/** @NoReturn */
function KillApp() {
    $a++;
}

function willKillApp() {
    $b++;
    KillApp();
    $unreachable_code++;
}

function wontkillapp() {
    $b++;
    if (rand(0, 1))
        KillApp();
    $reachable_code++;
}

?>