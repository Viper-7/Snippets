<?php
$secure=1;
require_once('config.php');
require_once('checkaddr.php');

$file = str_replace("\'","'",$_GET['file']);
header('Content-disposition: attachment; filename="' . basename($file) . '"');
header("Cache-Control:");
header("Content-Type: application/octet-stream");
readfile($file);
?>
