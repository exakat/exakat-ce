<?php

uasort($groups, function (array $a, array $b): int { return count($a) <=> count($b);});
uasort($groups, function (int $a, int $b): int { return $a > $b;});

?>