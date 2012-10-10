<?php
require_once('config.php');
require_once('connect.php');
$referer = parse_url($_SERVER['HTTP_REFERER']);
$validhost=0;

foreach ($domains as $host) {
	if(strpos('-' . $referer['host'], $host)) {
		$validhost=1;
	}
}

if ($secure == 1 && $validhost != 1 && !isset($_SESSION['username'])) { header('Location: /Browser/?logout=1'); die(); }
?>