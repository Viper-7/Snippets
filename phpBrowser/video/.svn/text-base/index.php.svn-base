<?php
	$secure=1;
	require('../Browser/checkaddr.php');
/*	if(!strpos($_SERVER['REQUEST_URI'],'/.')) {
		$tmp = fopen('http://' . str_replace('video/','flv/',gethostbyaddr($_SERVER['SERVER_ADDR']) . $_SERVER['REQUEST_URI']) . '&quality=medium', 'r');
		fclose($tmp);
		unset($tmp);
	}*/

	list($imdbid, $part)=@mysql_fetch_array(@mysql_query("SELECT imdbid, part FROM imdbfiles WHERE Filename LIKE '" . basename(urldecode($_GET['file'])) . "'"));
	list($maxpart)=@mysql_fetch_array(@mysql_query("SELECT MAX(part) FROM imdbfiles WHERE imdbid=$imdbid"));
	if($maxpart > $part) {
		list($nextfile)=@mysql_fetch_array(@mysql_query("SELECT Filename FROM imdbfiles WHERE imdbid=$imdbid AND part=" . ($part + 1)));
		$nextlink = " <A HREF=/video/?file=/opt/filestore/Movies/" . urlencode($nextfile)."><FONT COLOR='#444444'>Next Part</A>";
	}
?>
<HTML>
<head>
<TITLE><?php echo basename($_GET['file']); ?></TITLE>
</head>
<body BGCOLOR="black" TEXT="white" LINK="white" VLINK="white" ALINK="grey">
<TABLE HEIGHT=100% WIDTH=100% CELLSPACING=0 CELLPADDING=0><TR VALIGN=MIDDLE><TD ALIGN=CENTER>
<embed type="application/x-vlc-plugin"
         name="video1" id="video1"
         autoplay="yes" loop="yes" width="720" height="576"
         target="http://<?php echo str_replace('video/','video/video.php',gethostbyaddr($_SERVER['SERVER_ADDR']) . $_SERVER['REQUEST_URI']); ?>" />

<BR><BR>
<?php echo $nextlink; ?>
<BR><BR>
<a href="/vlc-0.9.7-git-win32.exe" onclick='document.video1.stop()'><FONT COLOR="#444444">VLC Player</a> (use Mozilla or ActiveX plugin)</FONT>
</TR></TD></TABLE>
</BODY>
<?php
/*
  <a href="javascript:;" onclick='document.video1.play()'>Play</a> &nbsp; &nbsp; 
  <a href="javascript:;" onclick='document.video1.pause()'>Pause</a> &nbsp; &nbsp; 
  <a href="javascript:;" onclick='document.video1.stop()'>Stop</a> &nbsp; &nbsp; 
  <a href="javascript:;" onclick='document.video1.fullscreen()'>Fullscreen</a> &nbsp; &nbsp; 
  <a href="javascript:;" onclick='document.video1.stop(); history.go(-1)'>Back</a> 
*/
?>