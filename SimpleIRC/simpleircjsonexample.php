<?php	
	include('simpleirc.php');
	
	$si = new simpleirc();
	
	$si->connect('WebIRC.GameSurge.net', 'tinder3') or die('Failed to connect to IRC server');
	
	echo json_encode($si->getchanlist('#nesreca'));
	
	$si->disconnect();
?>