<?php
	$secure=1;
	require_once('config.php');
	require_once('checkaddr.php');
	
	$dir = $_GET['dir'];
	$file = $_GET['file'];
	
	if(isset($dir)) {
		$ticket_no = substr(md5(uniqid(rand(), true)),0,8);
	
		chdir($tmpfolder);
		$dir = str_replace(' ', '\ ', $dir);
		
		header("Cache-Control:");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=" . urlencode($file) . ".tar.gz");
	
		$arr = explode('/', $dir);
		$name = $arr[count($arr)-1];
	
		exec("tar -vczf $tmpfolder/$ticket_no.tar.gz -C $dir ../$name");
		
		$dump = file_get_contents($ticket_no . ".tar.gz");
		echo $dump;
		unlink($ticket_no . ".tar.gz");
	} else {
		header("Location: index.php");
	}
?>