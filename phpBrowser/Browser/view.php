<?php
$secure=1;
require_once('config.php');

$file = str_replace("\'","'",$_GET['file']);
$type = mime_content_type($file);

$testfile = '-' . $file;
if(strpos($testfile,'/opt/') + strpos($testfile,'/mnt/') + strpos($testfile,'/tmp/') == 0) {
	die ('Fuck off hacker');
}

if ($type == 'application/octet-stream') {
	header('Content-disposition: attachment; filename="' . basename($file) . '"');
}

header("Cache-Control:");
header("Content-Type: $type");
	
streamFile($file, 0, 85);
?>
