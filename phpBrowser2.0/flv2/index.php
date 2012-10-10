<?php
	// Enable Security
	require_once('../Browser/checkaddr.php');
	
	if(isset($_GET['file'])) {
		// Encode the file or load it from the cache
		$filename = $_GET['file'];
		require('../Browser/buildvideo.php');

	} elseif(isset($_GET['ticket'])) {
		// Fetch the temporary file from the cache
		$ticket = $_GET['ticket'];
		list($filename, $quality) = @mysql_fetch_array(@mysql_query("SELECT Filename, Quality FROM flvTickets WHERE Ticket='" . $ticket . "'"));
		$file = 'streamflv.php/' . $ticket;

	} else {
		list($filename, $quality, $ticket) = @mysql_fetch_array(@mysql_query("SELECT Filename, Quality, Ticket FROM flvTickets ORDER BY Timestamp DESC LIMIT 1"));
		$file = 'streamflv.php/' . $ticket;
	}
	if ($quality == '')
		$quality = 'medium';
	
	list($topticket)=@mysql_fetch_array(@mysql_query("SELECT Ticket FROM flvTickets ORDER BY Timestamp DESC LIMIT 1"));
	if($ticket==$topticket)
		$quick = true;
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
			startingBufferLength: 2,
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
<CENTER><A HREF="cache.php">Browse Cache</A> &nbsp; &nbsp; <A HREF="?ticket=<?php echo $ticket; ?>">Direct Link</A><?php if($quick) { ?> &nbsp; &nbsp; <A HREF="http://viper-7.com/flowplayer">Quick Link</A><?php } ?></CENTER>
</TR></TD></TABLE>
</BODY></HTML>