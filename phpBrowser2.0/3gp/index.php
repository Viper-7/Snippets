<?php
	$quality = 'mobile';

	// Enable Security
	require_once('../Browser/checkaddr.php');
	
	if(!$3gp)
		die();
	
	$quality = 'mobile';

	if(isset($_GET['file'])) {
		// Encode the file or load it from the cache
		$filename = $_GET['file'];
		require('../Browser/buildvideo.php');
		header('Location: wait.php?ticket=' . $ticket);
		die();

	} elseif(isset($_GET['ticket'])) {
		// Fetch the temporary file from the cache
		$ticket = $_GET['ticket'];

		$running = false;
		$pid = 0;
		$out = shell_exec('ps -o pid,args ax | grep ' . $ticket . ' 2>/dev/null');
		$out = split("\n", $out);
		foreach($out as $pid) {
			if (strpos($pid,'grep'))
				continue;
			$pid = trim($pid);
			$pid = trim(substr($pid,0,strpos($pid, ' ')));

			if (strlen($pid) > 0)
				$running = true;
		}
		
		if($running) {
			header('Location: wait.php?ticket=' . $ticket);
			die();
		}

		list($filename, $quality, $keyed) = @mysql_fetch_array(@mysql_query("SELECT Filename, Quality, Keyed FROM flvTickets WHERE Ticket='" . $ticket . "'"));
		if(!$keyed) {
			header('Location: wait.php?ticket=' . $ticket);
			die();
		}			
		if($quality != 'mobile')
			header('Location: /flv?ticket=' . $ticket);
	} else {
		list($filename, $quality, $ticket) = @mysql_fetch_array(@mysql_query("SELECT Filename, Quality, Ticket FROM flvTickets WHERE Quality='mobile' ORDER BY Timestamp DESC LIMIT 1"));
	}
		
	if(!$ticket)
		header('Location: /Browser?quality=tiny');

	header('Location: rtsp://cerberus.viper-7.com/' . $ticket . '.3gp');
?>