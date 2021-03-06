<?php
	require_once('../Browser/checkaddr.php');
	
	if(!$filename) {
		// Read the filename from the URL
		$tmpfile = @str_replace('/3gp/streamflv.php/','',$_SERVER['REQUEST_URI']) . '.3gp';
		
		if($tmpfile) 
			$filename = $tmpfolder . '/' . $tmpfile;
		if(isset($_GET['file']))
			$filename = $tmpfolder . '/' . $_GET['file'];
	}
	
	// Load the streaming FLV starting position if supplied, to support seeking
	if(isset($_GET['start']))
		$start = $_GET['start'];
	else
		$start = 0;

	// Set headers to block caching and browsers from displaying the flv directly
	header("Content-type: video/3gpp\r\n");
	header('Content-disposition: attachment; filename="' . basename($filename) . '"' . "\r\n");
	header("Cache-Control: no-cache\r\n");
	header("Pragma: hack\r\n");
	header("Content-Transfer-Encoding: binary\r\n");

	// If our file is less than 16kb, we're not going to be able to stream it
	if (filesize($filename) < 1024*16) {
		header ("HTTP/1.0 505 Internal server error");
		return;
	
	// If the client has requests a partial file, return the correct HTTP header so the player doesn't freak out
	} elseif ($start != 0) {
		header('HTTP/1.0 206 Partial Content');
	
	// Everything worked! :O
	} else {
		header('HTTP/1.0 200 OK');
	}

	// Dump the file
	streamFile($filename, $start);	
?>