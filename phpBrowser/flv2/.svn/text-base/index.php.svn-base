<?php
	// Enable Security
	$secure=0;
	require_once('../Browser/checkaddr.php');
	if(isset($_GET['file'])) $filename = $_GET['file'];
	
	if(isset($_GET['imdbid'])) {
		list($filename) = @mysql_fetch_array(@mysql_query("SELECT Filename FROM imdbfiles WHERE imdbid=" . $_GET['imdbid']));
		if(!isset($quality)) { $quality = 'medium'; }
		$filename = '/opt/filestore/Movies/' . $filename;
		$file = $filename;
	}
	
	if(isset($_GET['quality'])) {
		$quality = $_GET['quality'];
		$_SESSION['quality'] = $quality;
	}

	if(isset($_GET['ticket'])) {
		// Fetch the temporary file from the cache
		$ticket = $_GET['ticket'];
		list($filename, $quality) = @mysql_fetch_array(@mysql_query("SELECT Filename, Quality FROM flvTickets WHERE Ticket='" . $ticket . "'"));
		$file = 'streamflv.php/' . $ticket;

	} elseif(!isset($file) &&!isset($filename) && !isset($_GET['file'])) {
		list($filename, $quality, $ticket) = @mysql_fetch_array(@mysql_query("SELECT Filename, Quality, Ticket FROM flvTickets ORDER BY Timestamp DESC LIMIT 1"));
		$file = 'streamflv.php/' . $ticket;
	}

	if($quality == 'mobile') {
		header('Location: /3gp?ticket=' . $ticket);
		die();
	}

	if($quality == 'direct') {
		header('Location: /video?file=' . urlencode($file));
		die();
	}
	
	if(!isset($ticket)) {
		if(isset($filename)) {
			// Encode the file or load it from the cache
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

	list($imdbid, $part)=@mysql_fetch_array(@mysql_query("SELECT imdbid, part FROM imdbfiles WHERE Filename LIKE '" . basename($filename) . "'"));
	list($maxpart)=@mysql_fetch_array(@mysql_query("SELECT MAX(part) FROM imdbfiles WHERE imdbid=$imdbid"));
	if($maxpart > $part) {
		list($nextfile)=@mysql_fetch_array(@mysql_query("SELECT Filename FROM imdbfiles WHERE imdbid=$imdbid AND part=" . ($part + 1)));
		$nextlink = " <A HREF=/flv/?file=/opt/filestore/Movies/" . urlencode($nextfile) . " TARGET='_blank'>Next Part</A>";
	}
?>
<HTML><HEAD><TITLE><?php echo str_replace('.',' ',substr(basename($filename),0,strrpos(basename($filename),'.')));?> (<?php echo ucfirst($quality); ?> Quality FLV)</TITLE>
	<script type="text/javascript" src="js/flashembed.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/common.css"/>

	<script>
	 flashembed("player", 
		{
			src:'FlowPlayerDark.swf',
			width: 720, 
			height: 540
		},
		
		{config: {   
			bufferLength: 10,
			startingBufferLength: 5,
			streamingServer: 'lighttpd',
			controlBarBackgroundColor:'0x2E303F',
			initialScale: 'fit',
			loop: false,
		        playList: [ {url: '<?php echo $file; ?>'} ],
			autoPlay: true
		}}
	);
	</script>
</HEAD>
<BODY BGCOLOR="#050608" TEXT="white" LINK="#333333" VLINK="#333333" ALINK="grey">
<TABLE HEIGHT=100% WIDTH=100% CELLSPACING=0 CELLPADDING=0><TR VALIGN=MIDDLE><TD ALIGN=CENTER>
<div id="player">The video player failed to load.</div>
<CENTER><A HREF="cache.php">Browse Cache</A> &nbsp; &nbsp; <A HREF="?ticket=<?php echo $ticket; ?>">Direct Link</A><?php if($quick) { ?> &nbsp; &nbsp; <A HREF="http://viper-7.com/flv">Quick Link</A> &nbsp; &nbsp;<?php } echo $nextlink; ?></CENTER>
</TR></TD></TABLE>
</BODY></HTML>