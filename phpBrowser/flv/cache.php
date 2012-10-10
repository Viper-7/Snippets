<?php
	$secure=1;
	require_once('../Browser/checkaddr.php');
?>
<HTML><HEAD><TITLE>Streaming FLV Cache</TITLE>
<script language="javascript" type="text/javascript">
	setTimeout("location.href='cache.php';",5000);
</script>
</HEAD><BODY LINK="#555555" ALINK="#CCCCCC" VLINK="#555555">
<?php
	// Process the cache flush request if received
	if(isset($_GET['del'])) {
		$del = $_GET['del'];
		if($del == 1) {
			ob_start();
			
			// Delete everything from the database cache
			Echo '<center>Deleting Tickets<BR/><B>'; ob_flush();
			mysql_query("DELETE FROM flvTickets");
			echo '<BR/>';
			
			// Report any records still left
			$result = mysql_query("SELECT Filename, Ticket FROM flvTickets");
			while(list($filename,$ticket) = mysql_fetch_array($result)) {
				echo '<i>' . $filename . '</i> - ' . $ticket . '.flv - WAS NOT DELETED!<BR/>'; ob_flush();
			}
			
			// Kill all ffmpeg servers
			echo '</B>Killing ffmpeg Processes<BR/><B>'; ob_flush();
			shell_exec("killall ffmpeg" . ' 2>/dev/null');
			echo '<BR/>';
			
			// Delete all cached files
			echo '</B>Removing FLV files<BR/><B>'; ob_flush();
			exec("rm -f $tmpfolder/*.flv" . ' 2>/dev/null');
			echo '</B>Removing 3GP files<BR/><B>'; ob_flush();
			exec("rm -f $tmpfolder/*.3gp" . ' 2>/dev/null');
			echo '</B>Removing log files<BR/><B>'; ob_flush();
			exec("rm -f $tmpfolder/*.log" . ' 2>/dev/null');
			echo '<BR/>';
			
			echo 'Done!</B><BR/>'; ob_flush();
			die();
		} else {
			@mysql_query("DELETE FROM flvTickets WHERE Ticket='" . $del . "'");
			
			$out = shell_exec('ps -o pid,args ax | grep ' . trim($del));
			$out = split("\n", $out);
			foreach($out as $pid) {
				if (strpos($pid,'grep'))
					continue;
				$pid = trim($pid);
				$pid = trim(substr($pid,0,strpos($pid, ' ')));
	
				if (strlen($pid) > 0)
					echo shell_exec('kill -9 ' . $pid);
			}
			
			exec('rm -f ' . $tmpfolder . '/' . $del . '.* 2>/dev/null');
		}
	}

	// Read a list of all flv files in our cache folder
	$files = split("\n", shell_exec('ls ' . $tmpfolder . '/*.flv 2>/dev/null') . "\n" . shell_exec('ls ' . $tmpfolder . '/*.3gp 2>/dev/null'));
	
	// Read all Tickets from the database
	$result = mysql_query("SELECT Filename, Ticket, Quality, Timestamp, Running, Resolution FROM flvTickets ORDER BY Quality, Timestamp DESC");
	
	// Display the table headers
	echo '<center><H1>Cached Videos</H1><BR/>';
	echo '<table width=1150 cellspacing=2>';
	echo '<tr><th># &nbsp;</th><th>Source File</th><th>Filename</th><th>Quality</th><th>Resolution</th><th>Size</th><th>Complete</th></tr>';
	
	$count = 0;
	
	// Loop through each Ticket in the database
	while(list($filename,$ticket,$quality,$timestamp,$running,$resolution) = mysql_fetch_array($result)) {
		$count += 1;
		
		if($running == 1)
			$complete = 'No';
		else
			$complete = 'Yes';
		
		// Loop through each file in the cache, and remove this file from our array of orphaned files
		for ($x=0;$x<count($files);$x++) {
			$curticket = array_pop($files);
			if ($curticket != '/tmp/' . $ticket . '.flv' && $curticket != '/tmp/' . $ticket . '.3gp')
				array_unshift($files, $curticket);
		}
		
		// Display the cached record
		echo '<TR><TD><center>' . $count . '  &nbsp;</center></TD>';
		
		if($quality == 'mobile') {
			if($count==1){
				echo '<TD><A HREF="http://viper-7.com/3gp"><i>' . basename(trim($filename)) . '</i></A></TD>';
			} else {
				echo '<TD><A HREF="/3gp?ticket=' . $ticket . '"><i>' . basename(trim($filename)) . '</i></A></TD>';
			}
			echo '<TD><a href="/' . $ticket . '.3gp">' . $ticket . '</a></TD>';
			echo '<TD>' . ucfirst(trim($quality)) . '</TD>';
			echo '<TD><center>' . $resolution . '</center></TD>';
			echo '<TD><center>' . get_filesize(@filesize($tmpfolder . '/' . $ticket . '.3gp')) . '</center></TD>';
		} else {
			if($count==1){
				echo '<TD><A HREF="http://viper-7.com/flv"><i>' . basename(trim($filename)) . '</i></A></TD>';
			} else {
				echo '<TD><A HREF="/flv?ticket=' . $ticket . '"><i>' . basename(trim($filename)) . '</i></A></TD>';
			}
			echo '<TD><a href="/' . $ticket . '.flv">' . $ticket . '</a></TD>';
			echo '<TD>' . ucfirst(trim($quality)) . '</TD>';
			echo '<TD><center>' . $resolution . '</center></TD>';
			echo '<TD><center>' . get_filesize(@filesize($tmpfolder . '/' . $ticket . '.flv')) . '</center></TD>';
		}
		
		echo '<TD><center>' . $complete . '</center></TD>';
		echo '</TR>';
	}
	echo '</table>';
	echo '<BR/><H1>Running Servers</H1><BR/>';
	echo '<table width=1000 cellspacing=2>';
	echo '<TR><TH>Filename</TH><TH>Quality</TH><TH>Resolution</TH><TH>Frame</TH><TH>FPS</TH><TH>Q</TH><TH>Size</TH><TH>Length Encoded</TH><TH>Output Bitrate</TH><TH></TH></TR>';
	$result = mysql_query("SELECT Filename, Ticket, Quality, Timestamp, Resolution FROM flvTickets WHERE Running=true");
	while(list($filename,$ticket,$quality,$timestamp,$resolution) = mysql_fetch_array($result)) {

		// Low quality is assumed if not supplied
		if($quality == '')
			$quality = 'medium';

		$ok = false;
		$out = shell_exec('ps -o pid,args ax | grep ' . $ticket . ' 2>/dev/null');
		$out = split("\n", $out);
		foreach($out as $pid) {
			if (strpos($pid,'grep'))
				continue;
			$pid = trim($pid);
			$pid = trim(substr($pid,0,strpos($pid, ' ')));

			if (strlen($pid) > 0)
				$ok = true;
		}
		
		if($ok==false) {
			mysql_query("UPDATE flvTickets SET Running=false WHERE Ticket='" . $ticket . "'");
			continue;
		}
		
		$file = fopen($tmpfolder . '/' . $ticket . '.log', 'r');
		$seek=4096;
		while (count($log) < 2) {
			fseek($file, filesize($tmpfolder . '/' . $ticket . '.log')-$seek);
			$log = fread($file,4096);
			$log = split("frame=", $log);
			$seek+=4096;
		}
		fclose($file);
		while (count($log) > 1)
			array_shift($log);
		
		$log = 'frame' . trim($log[0]);
		$frame = trim(array_shift(split('fps', array_pop(split('frame=', $log)))));
		$fps = trim(array_shift(split('q', array_pop(split('fps=', $log)))));
		$qual = trim(array_shift(split('size', array_pop(split('q=', $log)))));
		$size = trim(array_shift(split('time', array_pop(split('size=', $log)))));
		$size = get_filesize(substr($size,0,strlen($size)-2)*1024);
		$time = array_shift(split(' ', array_pop(split('time=', $log))));
		$minutes = intval($time / 60);
		$seconds = str_pad(round(intval($time) % 60,1),2,'0',STR_PAD_LEFT);
		$subseconds = substr($time - intval($time),strlen($time - intval($time)) - 1,1);
		$bitrate = trim(array_shift(split(' ',trim(str_replace('=','',array_pop(split('bitrate', $log)))))));
		
		if ($quality == 'mobile')
			echo '<TR><TD><center>' . $ticket . '.3gp</center></TD>';
		else
			echo '<TR><TD><center>' . $ticket . '.flv</center></TD>';
		echo '<TD><center>' . ucfirst($quality) . '</center></TD>';
		echo '<TD><center>' . $resolution . '</center></TD>';
		echo '<TD><center>' . $frame . '</center></TD>';
		echo '<TD><center>' . $fps . '</center></TD>';
		echo '<TD><center>' . $qual . '</center></TD>';
		echo '<TD><center>' . $size . '</center></TD>';
		echo '<TD><center>' . "$minutes minutes $seconds.$subseconds seconds" . '</center></TD>';
		echo '<TD><center>' . $bitrate . '</center></TD>';
		echo '</TR>';
	}
	echo '</table><BR/>';
?>
</BODY></HTML>