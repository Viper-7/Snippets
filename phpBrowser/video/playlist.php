<?php
	require_once('../Browser/checkaddr.php');
	if(isset($_GET['file'])) {
		$file = $_GET['file'];
		
		header("Content-type: application/octet-stream\r\n");
		header("Cache-Control: no-cache\r\n");
		header("Pragma: hack\r\n");

		if(substr($file,strlen($file)-5,5)=='mpcpl') {
			header('Content-disposition: attachment; filename="' . basename($file) . '"' . "\r\n");
			$pl = file_get_contents($file);
			if(!$local) {
				$pl = str_replace('\\','/',$pl);
				$pl = str_replace('//thor/c$/','http://www.viper-7.com/video/video.php?file=/mnt/thorc/',$pl);
				$pl = str_replace('//druss/shares/','http://www.viper-7.com/video/video.php?file=/mnt/druss/',$pl);
				$pl = str_replace(' ','%20',$pl);
			}
			echo $pl;
		} else {
			header('Content-disposition: attachment; filename="' . basename($file) . '.mpcpl"' . "\r\n");
			echo "MPCPLAYLIST\n";
			echo "1,type,0\n";
			echo "1,filename,http://www.viper-7.com/video/video.php?file=" . $file . "\n";
		}
	}
?>