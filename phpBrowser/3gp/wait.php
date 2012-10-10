<?php 
	header("Cache-Control: no-cache\r\n");

	if(isset($_GET['refresh']))
		$refresh = intval($_GET['refresh']) + 1;
	else
		$refresh = 1;

	if(!isset($_GET['ticket'])) 
		header('Location: /Browser'); 

	require_once('../Browser/checkaddr.php');
	
	ob_start();
?>
<HTML><HEAD><TITLE>Loading your movie</TITLE>
<script language="javascript" type="text/javascript">
	setTimeout("location.href='/3gp/wait.php?ticket=" . $ticket . "&refresh=" . $refresh . "';",5000);
</script>
</HEAD>
<body class="body" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor=#FAFAFA>
<link href="default.css" rel="stylesheet" type="text/css">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" bgcolor=#FAFAFA>
	<tr> 
		<td height="8" valign="top"></td>
	</tr>
	<tr> 
		<td height="34" align="center" valign="top">
			<table width="190" border="0" cellspacing="0" cellpadding="0">
				<tr> 
					<td>
						<table width="100%" height="10" border="0" cellpadding="0" cellspacing="0">
							<tr> 
								<td width="12" height="10"><img src="images/end_1.gif" width="12" height="10"></td>
								<td width="100%" height="10" background="images/glo_tile2.gif"></td>
								<td width="13" height="10"><img src="images/end_2.gif" width="13" height="10"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr> 
					<td width="100%">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr> 
								<td width=16 background="images/format_greybar_leftside.gif"></td>
								<td width="160" height="100%" bgcolor=#EEEEEE>
<TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=0 BORDER=0 BORDERWIDTH=0 BORDERHEIGHT=0>
<BR/>
<TR/>
	<TD/>
		<?php
			$ticket = htmlentities(trim($_GET['ticket']));
			list($filename,$quality,$dbpercent,$timestamp) = mysql_fetch_array(mysql_query("SELECT Filename, Quality, Percent, Timestamp FROM flvTickets WHERE Ticket='" . $ticket . "' LIMIT 1"));

			$running = checkprocess($ticket);
			
			if(file_exists($tmpfolder . '/public/' . $ticket . '.3gp'))
				$running = false;
			
			if($running) {
				$log = statusfromlog($tmpfolder . '/' . $ticket . '.log','frame=');

				$frame = trim(array_shift(split('fps', array_pop(split('frame=', $log)))));
				$fps = trim(array_shift(split('q', array_pop(split('fps=', $log)))));
				$qual = trim(array_shift(split('size', array_pop(split('q=', $log)))));
				$size = trim(array_shift(split('time', array_pop(split('size=', $log)))));
				$size = get_filesize(substr($size,0,strlen($size)-2)*1024);
				$time = intval(array_shift(split(' ', array_pop(split('time=', $log)))));
				$minutes = intval($time / 60);
				$seconds = str_pad(round(intval($time) % 60,1),2,'0',STR_PAD_LEFT);
				$subseconds = substr($time - intval($time),strlen($time - intval($time)) - 1,1);
				$bitrate = trim(array_pop(split('bitrate=', $log)));
	
				$orgtime = avlength($filename);
				$percent = round(($time / $orgtime) * 100,1);

				$secstaken = date_diff($timestamp);
				$remainest = intval(($secstaken / $percent) * (100 - $percent));
				
				$minsremain = intval($remainest / 60);
				$secsremain = $remainest % 60;
				
				$cursize = filesize($tmpfolder . '/' . $ticket . '.3gp');
				if($percent < 3)
					$sizemul = 110 + (0.1 * (100 - $percent));
				else
					$sizemul = 129 + (0.02 * (100 - $percent));
				$totalsize = get_filesize(intval(($cursize / $percent) * $sizemul));

				if(!strpos($percent,'.'))
					$percent .= '.0';
				if($percent > $dbpercent)
					@mysql_query("UPDATE flvTickets SET Percent=$percent WHERE Ticket='" . $ticket . "'");
				else
					$percent = $dbpercent;
				
				if($percent >= '99.8') {
					$running = false;
				} else {
					echo '<center>Please Wait...<BR/><BR/>';
					echo 'The server is encoding :<BR/>';
					echo '<B>' . htmlentities(str_replace('.',' ',basename(substr($filename,0,strrpos($filename,'.'))))) . '</B><BR/>';
					echo '<BR/>';
					echo $percent . '% - ';
					if($minsremain)
						echo $minsremain . ' mins ';
					echo $secsremain . ' secs left<BR/>';
					echo 'Estimated data usage : ' . $totalsize . '<BR/>';
					echo '<BR/>';
					echo '<A HREF="/3gp/wait.php?ticket=' . $ticket . '&refresh=' . $refresh . '">Refresh</A><BR/>';
					echo '<BR/>';
					echo '<A HREF="/Browser">Back to the Browser</a>';
					echo '</center>';
				}
			}
			
			if(!$running) {
				if(@filesize($tmpfolder . '/publish/' . $ticket . '.3gp') + @filesize($tmpfolder . '/' . $ticket . '.3gp') + 0 == 0) {
					echo '<CENTER><BR/><BR/><BR/>';
					echo 'An error occured encoding<BR/>';
					echo '<B>' . htmlentities(str_replace('.',' ',basename(substr($filename,0,strrpos($filename,'.'))))) . '<BR/></B>';
					echo '<BR/><BR/><BR/>';
					echo '<A HREF="/Browser">Back to the Browser</a></CENTER><BR/><BR/><BR/>';
				} else {
					list($keyed)=mysql_fetch_array(mysql_query("SELECT Keyed FROM flvTickets WHERE Ticket='" . $ticket . "'"));
					if(!$keyed) {
						$running=checkprocess($ticket . ' | grep MP4');
						if(!$running){
							sleep(1);
							$timestamp = time();
							$starttime = format_date($timestamp,'mysql-datetime');
							@mysql_query("UPDATE flvTickets SET Timestamp='" . $starttime . "' WHERE Ticket='" . $ticket . "'");
							exec('mp4box ' . $ticket . '.3gp &');
							sleep(5);
						}
						$running=checkprocess($ticket . ' | grep MP4');
						if(!$running && @filesize($tmpfolder . '/publish/' . $ticket . '.3gp')) {
							mysql_query("UPDATE flvTickets SET Running=false, Keyed=true WHERE Ticket='" . $ticket . "'");
							$keyed=1;
						} else {
							$log = statusfromlog('/tmp/mp4box.log', 'Hinting:');
							$percent = substr($log,strpos($log, '(')+1,strpos($log, '/')-(strpos($log, '(')+1));
							$totalsize = get_filesize(intval(filesize($tmpfolder . '/' . $ticket . '.3gp') * 1.3));
							
							echo '<center>Please Wait...<BR/><BR/>';
							echo 'The server is indexing :<BR/>';
							echo '<B>' . htmlentities(str_replace('.',' ',basename(substr($filename,0,strrpos($filename,'.'))))) . '</B><BR/>';
							echo '<BR/>';
							echo $percent . '%<BR/>';
							echo 'Estimated data usage : ' . $totalsize . '<BR/>';
							echo '<BR/>';
							echo '<A HREF="/3gp/wait.php?ticket=' . $ticket . '&refresh=' . $refresh . '">Refresh</A><BR/>';
							echo '<BR/>';
							echo '<A HREF="/Browser">Back to the Browser</a>';
							echo '</center>';
						}
					}
					if($keyed) {
						echo '<CENTER><BR/>';
						echo '<B>' . htmlentities(str_replace('.',' ',basename(substr($filename,0,strrpos($filename,'.'))))) . '<BR/></B>Is Ready!';
						echo '<BR/><BR/><BR/>';
						echo 'Total Size : ' . get_filesize(filesize($tmpfolder . '/publish/' . $ticket . '.3gp'));
						echo '<BR/><BR/><A HREF="rtsp://cerberus.viper-7.com/' . $ticket . '.3gp">Watch Now</A><BR/>';
						echo '<BR/>';
						echo '<A HREF="/Browser">Back to the Browser</a></CENTER><BR/>';
					}
				}
			}
		?>
	</TD>
</TR>
</TABLE>
								</td>
								<td width=15 background='images/right.jpg'></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr> 
					<td>
						<table width=100% cellspacing=0 cellpadding=0 border=0 background="images/bottom_grey_centre.gif">
							<TR>
								<TD align="left"><img src="images/bottom_grey_left.gif">
								<TD align="right"><img src="images/bottom_grey_right.gif">
							</TR>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</HTML>
<?php
	ob_flush();
?>