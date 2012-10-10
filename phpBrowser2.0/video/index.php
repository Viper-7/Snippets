<?php
	require('../Browser/checkaddr.php');
	$tmp = fopen('http://' . str_replace('video/','flv/',gethostbyaddr($_SERVER['SERVER_ADDR']) . $_SERVER['REQUEST_URI']) . '&quality=medium', 'r');
	fclose($tmp);
	unset($tmp);
?>
<HTML>
<head>
<TITLE><?php echo basename($_GET['file']); ?></TITLE>
</head>
<body BGCOLOR="black" TEXT="white" LINK="white" VLINK="white" ALINK="grey">
<TABLE HEIGHT=100% WIDTH=100% CELLSPACING=0 CELLPADDING=0><TR VALIGN=MIDDLE><TD ALIGN=CENTER>
<embed type="application/x-vlc-plugin"
         name="video1"
         autoplay="yes" loop="yes" width="720" height="576"
         target="http://<?php echo str_replace('video/','video/video.php',gethostbyaddr($_SERVER['SERVER_ADDR']) . $_SERVER['REQUEST_URI']); ?>" />

<BR><BR>
  <a href="javascript:;" onclick='document.video1.play()'>Play</a> &nbsp; &nbsp; 
  <a href="javascript:;" onclick='document.video1.pause()'>Pause</a> &nbsp; &nbsp; 
  <a href="javascript:;" onclick='document.video1.stop()'>Stop</a> &nbsp; &nbsp; 
  <a href="javascript:;" onclick='document.video1.fullscreen()'>Fullscreen</a> &nbsp; &nbsp; 
  <a href="javascript:;" onclick='document.video1.stop(); history.go(-1)'>Back</a> 
<BR><BR>
<a href="http://www.majorgeeks.com/VLC_media_player_d4674.html" onclick='document.video1.stop()'><FONT COLOR="#444444">VLC Player</a> (use Mozilla or ActiveX plugin)</FONT>
</TR></TD></TABLE>
</BODY>