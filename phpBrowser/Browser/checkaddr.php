<?php
require_once('config.php');
require_once('connect.php');
$referer = parse_url($_SERVER['HTTP_REFERER']);
if(!isset($validhost))
	$validhost=0;
if(!isset($local))
	$local=0;

foreach ($domains as $host) {
	if(strpos('-' . $referer['host'], $host)) {
		$validhost=1;
	}
}

$addr = split("\\.", $_SERVER['REMOTE_ADDR']);

if($addr[0] . $addr[1] == '100') {
	$local = 1;
	$validhost = 1;
}

if ($secure == 1 && $validhost != 1 && !isset($_SESSION['username'])) { header('Location: /Browser/?logout=1'); die(); }
?>