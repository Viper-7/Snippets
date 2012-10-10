<?php
require_once('../Browser/checkaddr.php');

$file = str_replace("\'","'",$_GET['file']);
header("Content-type: audio/mpeg\r\n");
header("Content-Length: " . filesize($file) . ";\r\n");
header('Content-disposition: attachment; filename="' . basename($file) . '"' . "\r\n");
header("Cache-Control: no-cache\r\n");
header("Pragma: hack\r\n");
header("Content-Transfer-Encoding: binary\r\n");

readfile($file);
?>