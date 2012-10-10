<?php
require_once('../config.php');
require_once('../checkaddr.php');
//require_once('mp3class.php');

$file = str_replace("\'","'",$_GET['file']);
$len = filesize($file);
header("Content-type: audio/mpeg\r\n");
header("Content-Length: $len;\r\n");
header('Content-disposition: attachment; filename="' . basename($file) . '"' . "\r\n");
header("Cache-Control: no-cache\r\n");
header("Pragma: hack\r\n");
header("Content-Transfer-Encoding: binary\r\n");

if(headers_sent($file2, $line)) {
    $handle = fopen('/tmp/hmm.txt','w+');
    fwrite($handle, var_export(headers_list(),true));
    fclose($handle);
} else {
    $handle = fopen('/tmp/hmm.txt','w+');
    fwrite($handle, "wtf");
    fclose($handle);
}

readfile($file);
?>