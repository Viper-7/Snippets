<?php
require_once('../Browser/checkaddr.php');

$file = str_replace("\'","'",$_GET['file']);

if(isset($_GET['start'])) {
	streamFile($file,$_GET['start']);
} else {
	readfile($file);
}
?>