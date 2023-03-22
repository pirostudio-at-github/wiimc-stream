<?php
$stream = fopen("http://riitube.rc24.xyz/wiimc/dailymotion/index.cgi","r");
$content = stream_get_contents($stream);
header("Content-Type: text/plain;charset=UTF-8;");
print($content);
