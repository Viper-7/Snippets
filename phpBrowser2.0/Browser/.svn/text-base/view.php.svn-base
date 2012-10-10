<?php
require_once('config.php');
require_once('checkaddr.php');

$file = str_replace("\'","'",$_GET['file']);
$type = mime_content_type($file);

header("Content-Type: $type");
header("Cache-Control:");

if ($type == 'application/octet-stream') {
	header('Content-disposition: attachment; filename="' . basename($file) . '"');
}
	
readfile($file);
?>
