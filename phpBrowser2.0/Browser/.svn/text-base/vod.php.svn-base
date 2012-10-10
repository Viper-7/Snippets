<?php
	$secure=1;
	require_once('config.php');
	require_once('checkaddr.php');

	if (isset($_SESSION['quality'])) { $quality=$_SESSION['quality']; }
	if (isset($_POST['quality'])) { $quality=$_POST['quality']; }
	if (isset($_POST['tcor'])) { $tcor=$_POST['tcor']; }
	if (isset($_POST['file'])) { $file=$_POST['file']; }
	if (isset($_POST['dir'])) { $dir=$_POST['dir']; }
	if (isset($_POST['act'])) { $act=$_POST['act']; }
	if (isset($_GET['file'])) { $file=$_GET['file']; }
	if (isset($_GET['dir'])) { $dir=$_GET['dir']; }
	if (isset($_GET['act'])) { $act=$_GET['act']; }
	if (isset($_GET['quality'])) { $quality=$_GET['quality']; }
	if (isset($quality)) { $_SESSION['quality']=$quality; }
	if (!isset($quality)) { $quality=0; }
	
	switch($quality) {
		case 0:
			include('vodquality.php');
			die();
			break;
		case 1:
			$vbr=160;
			$abr=48;
			break;
		case 2:
			$vbr=288;
			$abr=64;
			break;
		case 3:
			$vbr=384;
			$abr=72;
			break;
		case 4:
			$vbr=640;
			$abr=128;
			break;
		case 5:
			$vbr=0;
			$abr=0;
			break;
	}
	
	if ($vod==0) { die(); }
	
	$path = $basedir . $dir . '/' . $file;

	$tok = array(" ","-","[","]","(",")","#","&","@");
	$name = str_replace($tok,"_",$file);
	$name = substr($name, 0, strpos($name,"."));

	if($act == 'close'){
		$cmd = "del $name";
	} else {
		if ($tcor) {
			$cmd = "new $name vod enabled input \"$path\" $tcor";
		} elseif ($vbr > 0) { 
			$cmd = "new $name vod enabled input \"$path\" output #transcode{vcodec=mp4v,acodec=mpga,vb=$vbr,ab=$abr,channels=2,fps=24}";
		} else {
			$cmd = "new $name vod enabled input \"$path\" output";
		}
	}

	$fp = @fsockopen("localhost",$vodport,$errno,$errstr,30);
	if (!$fp) {
		echo "<CENTER><h1>VOD Server Offline!</H1><BR><BR>";
		echo "Run 'sudo vlc --daemon --ttl 12 -I telnet' on your server to start the VOD Server";
		echo "<BR><BR>Or hit refresh to try again";
	} else {
		fputs($fp,"admin\r\n");
		sleep(1);
		fputs($fp,"$cmd\r\n");
		sleep(1);
		fputs($fp,"quit\r\n");
		fclose($fp);
		if ($act == 'close') {
			header("Location: index.php");
		} else {
			$link = "rtsp://$serverurl/$name";
			?>
			<body bgcolor=#000000 text=#ffffff link=#999999 alink=#cccccc vlink=#999999><center><BR><BR>
			<embed type="application/x-vlc-plugin"
			         name="video1"
			         autoplay="no" loop="yes" width="720" height="480"
			         target="<?=$link?>" />
			<BR>
			  <a href="javascript:;" onclick='document.video1.play()'>Play</a> &nbsp; &nbsp; 
			  <a href="javascript:;" onclick='document.video1.pause()'>Pause</a> &nbsp; &nbsp; 
			  <a href="javascript:;" onclick='document.video1.stop()'>Stop</a> &nbsp; &nbsp; 
			  <a href="javascript:;" onclick='document.video1.fullscreen()'>Fullscreen</a>
			<?
			echo "<BR><BR><BR>Open the following link in VLC:<br>\n";
			echo "<a href=$link>$link</a>";
			echo "<BR><BR>";
			echo "<a href=vod.php?file=" . urlencode($file) . "&dir=" . urlencode($dir) . "&act=close&user=$username&pass=$password onclick='document.video1.stop()'>Close Server</a>";
			echo "<BR><BR><BR>NOTE: Do NOT refresh this page!<BR><BR>";
			echo "Click Play to start watching your video.<BR>Click Stop then Play to reconnect.<BR>Click Close Server and reopen your media file to restart the server.<BR>Please close the server when you are done to save server resources.<BR>";
		}
	}
?>