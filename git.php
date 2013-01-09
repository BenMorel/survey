<?php

require 'common.php';

if ($argc == 1) {
    printf('Usage: %s GIT_URL' . PHP_EOL, $argv[0]);
    exit(1);
}

$repo = $argv[1];
$temp = getTemporaryFileName();

system("git clone $repo $temp");

$surveyor = new Surveyor();
$counters = $surveyor->survey($temp);

foreach ($counters as $pattern => $count) {
    printf('%s : %s occurrences found.' . PHP_EOL, $pattern, $count);
}

system("rm -rf $temp");
