<?php
	require_once('config.php');
	require_once('checkaddr.php');
	require_once('mp3class.php');
	
	if ($_GET["dir"] . $_GET["symlink"]) {
		$ticket_no = substr(md5(uniqid(rand(), true)),0,8);
		$dir = $basedir . '/' . $_GET['dir'];
		$symlink = $_GET['symlink'];
		if ($symlink) { if ($dir) { $newdir = $dir . '/' . $symlink; } else { $newdir = $basedir . '/' . $symlink; }} else { $newdir = $dir; }
		chdir($newdir);
		exec('ls -1 *.mp3', $contents);
		$count = 0;
		$arr = explode('/', $newdir);
		$name = $arr[count($arr)-1];
	
		$output = "[playlist]\n";
		$output .= "NumberOfEntries=";
		$output .= count($contents) - 1 . "\n";
		$output .= "Version=2\n\n";
	
		if ($_GET['shuffle']) {
			for ($x=0;$x<4;$x++) {
				shuffle($contents);
			}
		}
	
		foreach ($contents as $entry) {
			// Ignore files < 300k (usually ads)
			if (filesize($newdir . "/" . $entry) > 307200) {
				$mp3 = new MP3($newdir . "/" . $entry); 
				$mp3->get_info(); 
		
				$output .= "File$count=http://$serverurl/$browserdir/mp3/?file=" . urlencode($newdir . "/" . $entry) . "\n";
				$title = explode(".", $entry);
				$title = $title[0];
				
				$output .= "Title$count=$title\n";
				$output .= "Length$count=";
				$output .= $mp3->info["length"];
				$output .= "\n\n";
			}
			$count += 1;
		}
	} elseif ($_GET['server']) {
		$server = $_GET['server'];
		$name = $_GET['name'];
		$output = "[playlist]\n";
		$output .= "NumberOfEntries=1\n";
		$output .= "\n";
		$output .= "File1=http://$server\n";
		$output .= "Title1=$name\n";
		$output .= "Length1=-1\n";
		$output .= "\n";
		$output .= "Version=2\n";
	} else {
		mail('viper7@viper-7.com','Noob hackin yer server','_SERVER:	' . var_dump($_SERVER) . "\n_GET:	" . var_dump($_GET) . "\n_POST:	" . var_dump($_POST) . "\n" . 'Host:	' .  gethostbyaddr($_SERVER['REMOTE_ADDR']),'From: Kodiak <viper7@viper-7.com>');
		die ("\nfail...");
	}
	
	header('Content-disposition: attachment; filename="' . $name . '.pls"');
	header("Cache-Control:");
	header("Content-Type: application/octet-stream");
	echo $output;
?>
