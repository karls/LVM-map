<?php

$url = "http://www.maaamet.ee/esmap/scripts/lestgeo.exe?CMD=LTG&CMDX=6473065&CMDY=529418";
$data = file_get_contents($url);

//$fp = fopen($url, 'r');

//$data = fread($fp, 40000);
//fclose($fp);

print $data;
?>
