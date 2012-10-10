<?php
require_once('../Browser/checkaddr.php');

$file = str_replace("\'","'",$_GET['file']);
$len = filesize($file);

header("Content-type: " . mime_content_type($file) . "\r\n");
header("Content-Length: $len;\r\n");
header('Content-disposition: attachment; filename="' . basename($file) . '"' . "\r\n");
header("Cache-Control: no-cache\r\n");
header("Pragma: hack\r\n");
header("Content-Transfer-Encoding: binary\r\n");

readfile($file);
?>