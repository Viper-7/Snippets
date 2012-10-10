<?php
	// Enable Security
	require_once('../Browser/checkaddr.php');

	if(isset($_GET['quality'])) {
		$quality = $_GET['quality'];
		$_SESSION['quality'] = $quality;
	}

	if(isset($_GET['ticket'])) {
		// Fetch the temporary file from the cache
		$ticket = $_GET['ticket'];
		list($filename, $quality) = @mysql_fetch_array(@mysql_query("SELECT Filename, Quality FROM flvTickets WHERE Ticket='" . $ticket . "'"));
		$file = 'streamflv.php/' . $ticket;

	} elseif(!isset($file) && !isset($_GET['file'])) {
		list($filename, $quality, $ticket) = @mysql_fetch_array(@mysql_query("SELECT Filename, Quality, Ticket FROM flvTickets ORDER BY Timestamp DESC LIMIT 1"));
		$file = 'streamflv.php/' . $ticket;
	}

	if($quality == 'mobile') {
		header('Location: /3gp?ticket=' . $ticket);
		die();
	}

	if(!isset($ticket)) {
		if(isset($_GET['file'])) {
			// Encode the file or load it from the cache
			$filename = $_GET['file'];
			if(!isset($quality)) 
				$quality='medium';
			require('../Browser/buildvideo.php');
		} else {
			header ("HTTP/1.0 505 Internal server error");
		}
	}

	list($topticket)=@mysql_fetch_array(@mysql_query("SELECT Ticket FROM flvTickets ORDER BY Timestamp DESC LIMIT 1"));
	if($ticket==$topticket)
		$quick = true;
		
	$flashvars = 'file=' . trim($ticket) . '.flv&streamer=streamflv.php&bufferlength=2&screencolor=000000&backcolor=2E303F&frontcolor=A0AAC0&lightcolor=C0D5E0&autostart=true';
?>
<HTML><HEAD><TITLE><?php echo str_replace('.',' ',substr(basename($filename),0,strrpos(basename($filename),'.')));?> (<?php echo ucfirst($quality); ?> Quality FLV)</TITLE>
<script type='text/javascript' src='swfobject.js'></script>
</HEAD>
<BODY BGCOLOR="#050608" TEXT="white" LINK="#333333" VLINK="#333333" ALINK="grey">
<TABLE HEIGHT=100% WIDTH=100% CELLSPACING=0 CELLPADDING=0><TR VALIGN=MIDDLE><TD ALIGN=CENTER>
<p id='flvplayer' class="media">
	<object width="512" height="384" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0">
	<param name="flashvars" value="<?=$flashvars?>" />
	<param name="movie" value="player.swf" />
	<embed src="player.swf" width="320" height="240" bgcolor="#FFFFFF" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="<?=$flashvars?>" />
	</object>
</p>
<script type='text/javascript'>
  var s1 = new SWFObject('player.swf','ply','720','576','9','#ffffff');
  s1.addParam('allowfullscreen','true');
  s1.addParam('flashvars','<?=$flashvars?>');
  s1.write('flvplayer');
</script>
<CENTER><A HREF="/flv2/?ticket=<?=$ticket?>">Alternate Player</A> &nbsp; &nbsp; <A HREF="cache.php">Browse Cache</A> &nbsp; &nbsp; <A HREF="?ticket=<?php echo $ticket; ?>">Direct Link</A><?php if($quick) { ?> &nbsp; &nbsp; <A HREF="http://viper-7.com/flowplayer">Quick Link</A><?php } ?></CENTER>
</TR></TD></TABLE>
</BODY></HTML>