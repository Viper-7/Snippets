<?php
	$secure=1;
	require('../Browser/checkaddr.php');
	$file = str_replace("\'","'",$_GET['file']);
	$ticket = '';
	
	// Kill servers if we're over our limit, and cleanup the file if it didn't get completed
	$result = mysql_query("SELECT Ticket FROM flvTickets ORDER BY Timestamp DESC", $mysql);
	
	$count = 0;
	
	// Set all tickets as not running, since we'll be checking them all
	mysql_query("UPDATE flvTickets SET Running=false");
	
	// Loop through each ticket in the database over our ffmpeg_max_servers limit
	while (list($ticket) = mysql_fetch_array($result)) {
		
		// Check if theres an ffmpeg server running for the ticket
		$out = shell_exec('ps -o pid,args ax | grep ' . $ticket . ' 2>/dev/null');
		$out = split("\n", $out);
		foreach($out as $pid) {
			
			// Ignore our ps process
			if (strpos($pid,'grep'))
				continue;
				
			// Strip the result down to a pid
			$pid = trim($pid);
			$pid = trim(substr($pid,0,strpos($pid, ' ')));
			
			// If we've found a server pid for this ticket
			if (strlen($pid) > 0) { 
				if ($count < $ffmpeg_max_servers-1) {
					// Valid server, let it continue and update the database with it's status
					@mysql_query("UPDATE flvTickets SET Running=true WHERE Ticket='" . $ticket . "'");
					$count += 1;
				} else {
					// Kill the ffmpeg process
					@shell_exec('kill -9 ' . $pid); 
					
					// Delete the incomplete file
					@unlink ($tmpfolder . '/' . $ticket . '.*');
					
					// And remove it from the flv cache
					@mysql_query("DELETE FROM flvTickets WHERE Ticket='" . $ticket . "'");
				}
			}
		}
	}

	// Delete a file to make room if we're over the cached files limit
	$result = mysql_query("SELECT Ticket FROM flvTickets WHERE Quality='" . $quality . "' ORDER BY Timestamp DESC LIMIT " . ($ffmpeg_max_files[$quality]-1) . ", 2", $mysql);
	while (list($ticket) = mysql_fetch_array($result)) {
		@unlink ($tmpfolder . '/' . $ticket . '.*');
		@mysql_query("DELETE FROM flvTickets WHERE Ticket='" . $ticket . "'");
	}
	
	// Check if the requested video is in the flv cache
	$result = mysql_query("SELECT Ticket FROM flvTickets WHERE Filename='" . mysql_real_escape_string($file) . "' AND Quality='" . $quality . "'", $mysql);


	// Setup for encoding if we don't have a cached copy
	if (@mysql_num_rows($result) == 0) {
		
		// Allocate a unique ticket number and update the flv cache
		$ticket = substr(md5(uniqid(rand(), true)),0,8);
		$filename = $file;

		if($quality == 'mobile') {
			$dim = '176x144';
		} else {
			$stats = shell_exec("avidentify " . escapeshellarg($file) . ' 2>/dev/null');
			$stats = trim(array_shift(split("\n",array_pop(split('Video:',$stats)))));
			$tok = strrpos($stats, ' ');
			$orgdim = substr($stats,$tok,strlen($stats)-$tok);
			$tok = strpos($orgdim, 'x');
			$orgw = substr($orgdim,0,$tok);
			$orgh = substr($orgdim,$tok+1,strlen($orgdim)-$tok);
			if($orgw > 0)
				$orgratio = $orgh / $orgw;
			else
				$orgratio = 3 / 4;
			$width='512';
			if($quality == 'high')
				$width='720';
			if($quality == 'tiny')
				$width='160';
			$height=round(($width * $orgratio)/16,0)*16;
			$dim = $width . 'x' . $height;
		}
		
		$timestamp = time();
		$starttime = format_date($timestamp,'mysql-datetime');
		
		mysql_query("INSERT INTO flvTickets SET Filename='" . mysql_real_escape_string($filename) . "', Ticket='" . $ticket . "', Quality='" . $quality . "', Resolution='" . $dim . "', Running=true, Timestamp='" . $starttime . "'", $mysql);

		// Start the appropriate encoder
		if($quality == 'high')
			$exec = "ffmp2 " . escapeshellarg($file) . ' ' . escapeshellarg($dim) . ' ' . escapeshellarg($tmpfolder . '/' . $ticket . '.flv') . ' ' . escapeshellarg($tmpfolder . '/' . $ticket . '.log');
		elseif($quality == 'tiny')
			$exec = "ffmp3 " . escapeshellarg($file) . ' ' . escapeshellarg($dim) . ' ' . escapeshellarg($tmpfolder . '/' . $ticket . '.flv') . ' ' . escapeshellarg($tmpfolder . '/' . $ticket . '.log');
		elseif($quality == 'mobile')
			$exec = "ffmp4 " . escapeshellarg($file) . ' ' . escapeshellarg($dim) . ' ' . escapeshellarg($tmpfolder . '/' . $ticket . '.3gp') . ' ' . escapeshellarg($tmpfolder . '/' . $ticket . '.log');
		else
			$exec = "ffmp " . escapeshellarg($file) . ' ' . escapeshellarg($dim) . ' ' . escapeshellarg($tmpfolder . '/' . $ticket . '.flv') . ' ' . escapeshellarg($tmpfolder . '/' . $ticket . '.log');
		exec($exec);
		
		if($quality == 'mobile') {
			header('Location: wait.php?ticket=' . $ticket);
		}

		if($quality == 'mobile')
			$file = '/' . $ticket . '.3gp';
		else
			$file = '/' . $ticket . '.flv';
		
		sleep(2);
		
		// Wait up to 20 seconds for ffmpeg to produce data
		$size = 0;
		$size = @filesize($tmpfolder . $file);
		while ($size == 0) {
			if ($count == 20) { die ('Error creating video, probably corrupt input.<BR>' . $exec); }
			sleep(1);
			$count += 1;
			clearstatcache();
			$size = @filesize($tmpfolder . $file);
		}

		// Wait for 128kb of data for mobile quality videos, 1mb for tiny quality, 2mb for low quality, and 3mb for high quality
		if ($quality == 'high') {
			$minsize = (3072*1024);
		} elseif ($quality == 'tiny') {
			$minsize = (1024*1024);
		} elseif ($quality == 'mobile') {
			$minsize = (128*1024);
		} else {
			$minsize = (2048*1024);
		}

		// Wait until ffmpeg produces our minimum amount of data
		while ($size < $minsize) {
			clearstatcache();
			$size = filesize($tmpfolder . $file);
			sleep(1);
		}
	} else {
		// Fetch the ticket filename from the cache
		list($ticket) = mysql_fetch_array($result);
		if($quality == 'mobile') {
			header('Location: wait.php?ticket=' . $ticket);
		}
	}

	
	// Check if the file is complete, if it is, allow direct access. Otherwise use the robust streaming script.
	sleep(1);
	clearstatcache();
	
	if($quality == 'mobile') 
		$path = '/' . $ticket . '.3gp';
	else
		$path = '/' . $ticket . '.flv';
		
	if(filesize($tmpfolder . $path) == $size) {
		$file = $path;
		$streaming=0;
	} else {
		$file = 'streamflv.php/' . $ticket;
		$streaming=1;
	}
?>