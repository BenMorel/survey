<?php

require 'common.php';

if ($argc == 1) {
    printf('Usage: %s ZIP_URL' . PHP_EOL, $argv[0]);
    exit(1);
}

$zipurl = $argv[1];
$zipfile = getTemporaryFileName() . '.zip';
$temp = getTemporaryFileName();

system("wget -O $zipfile $zipurl");
system("unzip -d $temp $zipfile");

$surveyor = new Surveyor();
$counters = $surveyor->survey($temp);

foreach ($counters as $pattern => $count) {
    printf('%s : %s occurrences found.' . PHP_EOL, $pattern, $count);
}

system("rm -f $zipfile");
system("rm -rf $temp");
