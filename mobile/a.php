<?php

$str = "http://www.jb51.net";
$isMatched = preg_match('/^((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+/', $str, $matches);
var_dump($isMatched, $matches);

