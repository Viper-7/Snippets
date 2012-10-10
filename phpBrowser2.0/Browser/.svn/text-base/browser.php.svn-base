<?php
require_once('checkaddr.php');

if ($needlogin) {
	echo "<BR><BR><center><form action='$PHP_SELF' method='POST'><table width=190><TR><TD>Username</TD><TD><input type=text name=user size=14></TD></TR>";
	echo "<TR><TD>Password</TD><TD><input type=password name=pass size=14></TD></TR>";
	echo "<TR><TD colspan=2><center><input type=submit name=submit value='Login'>";
	if ($mkpasswd) {
		echo " &nbsp; <input type=submit name=mkpasswd value='Mkpasswd'>";
	}
	echo "</TD></TR>";
	echo "</form></table>";
} else {
	$dir = $_GET['dir'];
	$symlink = $_GET['symlink'];
	
	if(!isset($dir)) {
		$dir = "";
	}
	
	chdir($basedir . '/' . $dir);
	
	// Get a clean copy of the current dir
	$curdir = rtrim(getcwd());
	$curdir = str_replace($basedir, '', $curdir);
	
	// Replace UNIX Style slashes with Windows style
	$curdir = eregi_replace("/", "\\", $curdir);
	
	// Remove the leading slash
	$curdir = substr($curdir,1);
	
	// Show the current directory
	echo("<font class='bodyHD'>");
	if ($symlink == "") {
		if ($curdir == "") {
			$curdir = $server_name;
		} else {
			$curdir = $server_name . "\\" . $curdir;
		}
	} else {
		$symdir = eregi_replace("/", "\\", $symlink);
		if ($curdir == "") {
			$curdir = $server_name . "\\" . $symdir;
		} else {
			$curdir = $server_name . "\\" . $curdir . "\\" . $symdir;
		}
	}
	echo($curdir);
	echo("</font>");
	if ($curdir) { echo("<BR/><BR/>"); } 
	
	if ($symlink) {
		$pwd = getcwd();
		$symlink = str_replace("\'","'",$symlink);
		chdir($pwd . '/' . $symlink);
	}
	
	// Get the current directory Listing
	exec('ls -1', $contents);
	
	if ($symlink) {
		$symbase = getcwd();
		chdir($pwd);
	}
	
	// If the current directory is not the root dir, display an option to go back a level
	if ($symlink) {
		if($quality != 'mobile')
			echo("<img src=images/spacer.gif width=13 height=16> ");
		echo("<img src=images/folder.gif> ");
		$arr = explode('/', $symlink);
		if (count($arr) > 1) {
			array_pop($arr);
			$oldlink = implode('/', $arr);
			echo("<a href='?dir=$dir&symlink=$oldlink'>[Up One Level]</a><BR/>\n");
		} else {
			echo("<a href='?dir=$dir'>[Up One Level]</a><BR/>\n");
		}
	} else {
		if ($dir != "") {
			if($quality != 'mobile')
				echo("<img src=images/spacer.gif width=13 height=16> ");
			echo("<img src=images/folder.gif> ");
			$olddir = getcwd();
			$olddir = str_replace($basedir, '', $olddir);
			$arr = explode('/', $olddir);
			array_pop($arr);
			$olddir = implode('/', $arr);
			echo("<a href='?dir=" . urlencode($olddir) . "'>[Up One Level]</a><BR/>\n");
		}
	}
	
	// Get a clean copy of the current directory
	$olddir = getcwd();
	$olddir = str_replace($basedir, '', $olddir);
	
	// Display all folders with the folder icon
	foreach ($contents as $entry) {
		$isfolder=false;
		if ($symlink) {
			$islink=true;
			if (is_dir($symbase . '/' . $entry . '/')) {
				@chdir($symbase . '/' . $entry);
				$pwd=getcwd();
				$pos=str_replace($symbase, '', $pwd);
				if ($pos) {
					$isfolder=true;
				}
			}
		} else {
			$islink=false;
			$pwd=getcwd();
			if (is_dir($basedir . $dir . '/' . $entry . '/')) {
				@chdir($basedir . $dir . '/' . $entry);
				if ($pwd != getcwd()) {
					$isfolder=true;
				}
				$pwd="blah" . getcwd();
				$pos=strpos($pwd, $basedir);
				if (($pos == '0')) {
					$islink=true;
				}
			}
		}
	
		// Reset to the working directory
		chdir($basedir . $dir);
		
		// If the current item is a folder
		if ($isfolder || ($islink && $isfolder)) {
			if($quality != 'mobile')
				echo("<img src=images/spacer.gif width=13 height=16> ");
			echo("<img src=images/folder.gif> ");
			// if we are not in the root directory
			if ($islink) {
				if ($symlink) {
					// display the link, with the current directory & symlink then the item name
					echo "<a href='?dir=$olddir&symlink=" . urlencode($symlink) . "/" . urlencode($entry) . "'>" . $entry . "</a>";
				} else {
					// display the link, with the current directory & symlink then the item name
					echo "<a href='?dir=$olddir&symlink=" . urlencode($entry) . "'>" . $entry . "</a>";
				}
			} elseif ($olddir != "/") {
				// display the link, with the current directory then the item name
				echo "<a href='?dir=" . urlencode($olddir) . "/" . urlencode($entry) . "'>" . $entry . "</a>";
			} else {
				// display the link, with just the item name
				echo "<a href='?dir=/" . urlencode($entry) . "'>" . $entry . "</a>";
			}
			// display debugging information
			if ($debug == 1) { echo (" [" . getcwd() . "/" . $entry . "]"); }
			echo "<br />\n";
		}
	}
	
	// Get a clean copy of the current directory
	$olddir = getcwd();
	$olddir = str_replace($basedir, '', $olddir);
	
	if ($symlink) {
		$symlink = '/' . $symlink;
	}

	$mp3count=0;
	
	// Display all files with the file icon
	foreach ($contents as $entry) {
		$isfolder=false;
		if ($symlink) {
			$islink=true;
			if (is_dir($symbase . '/' . $entry . '/')) {
				@chdir($symbase . '/' . $entry);
				$pwd=getcwd();
				$pos=str_replace($symbase, '', $pwd);
				if ($pos) {
					$isfolder=true;
				}
			}
		} else {
			$islink=false;
			$pwd=getcwd();
			if (is_dir($basedir . $dir . '/' . $entry . '/')) {
				@chdir($basedir . $dir . '/' . $entry);
				if ($pwd != getcwd()) {
					$isfolder=true;
				}
				$pwd="-" . getcwd();
				$pos=strpos($pwd, $basedir);
				if (($pos == '0')) {
					$islink=true;
				}
			}
		}
	
		// Reset to the working directory
		chdir($basedir . $dir);
	
		// If the item is NOT a folder
		if (!$isfolder) {
			// Display the file icon and the item name
			if ($symlink) {
				echo("<a href=download.php?file=" . urlencode($symbase . "/" . $entry) . ">");
			} else {
				echo("<a href=download.php?file=" . urlencode($basedir) . urlencode($dir) . "/" . urlencode($entry) . ">");
			}
			
			echo("<img src='images/arrow.gif' border=0 alt='Download'></a> ");
			
			$ext = substr($entry,strrpos($entry,".") + 1);
			if (($ext == 'avi' || $ext == 'mpg' || $ext == 'mpeg' || $ext == 'mkv' || $ext == 'vob' || $ext == 'mov' || $ext == 'wmv' || $ext == 'ogm' || $ext == 'flv' ||
			    $ext == 'AVI' || $ext == 'MPG' || $ext == 'MPEG' || $ext == 'MKV' || $ext == 'VOB' || $ext == 'MOV' || $ext == 'WMV' || $ext == 'OGM' || $ext == 'FLV') && ($vod || $flv) ) {
				if ($vod) {
					if ($ext == 'avi' || $ext == 'mkv') {
						echo("<a href='javascript:popUp(\"vodquality.php?dir=" . str_replace('%2F','/',urlencode($dir . $symlink)) . "&file=$entry&act=open\")'>");
					} else {
						echo("<a href='vod.php?dir=" . str_replace('%2F','/',urlencode($dir . $symlink)) . "&file=$entry&act=open'>");
					}
				}
				if ($flv) {
					if ($quality == 'direct') {
						if ($symlink) {
							echo("<a href=/video/?file=" . urlencode($symbase . "/" . $entry) . " target=_blank>");
						} else {
							echo("<a href=/video/?file=" . urlencode($basedir) . urlencode($dir) . "/" . urlencode($entry) . " target=_blank>");
						}
					} elseif ($quality == 'mobile' && $3gp) {
						if ($symlink) {
							echo("<a href=/3gp/?file=" . urlencode($symbase . "/" . $entry) . " target=_blank>");
						} else {
							echo("<a href=/3gp/?file=" . urlencode($basedir) . urlencode($dir) . "/" . urlencode($entry) . " target=_blank>");
						}
					} elseif ($alternate_flv_player) {
						if ($symlink) {
							echo("<a href=/flv/?file=" . urlencode($symbase . "/" . $entry) . " target=_blank>");
						} else {
							echo("<a href=/flv/?file=" . urlencode($basedir) . urlencode($dir) . "/" . urlencode($entry) . " target=_blank>");
						}
					} else {
						if ($symlink) {
							echo("<a href=/flv/?file=" . urlencode($symbase . "/" . $entry) . " target=_blank>");
						} else {
							echo("<a href=/flv/?file=" . urlencode($basedir) . urlencode($dir) . "/" . urlencode($entry) . " target=_blank>");
						}
					}
				}
				echo("<img src='images/movie.png' border=0 alt='Movie'></a> ");
			} elseif ($ext == 'mp3') {
				$mp3count += 1;
				if ($symlink) {
					echo("<a href=/mp3/?file=" . urlencode($symbase . "/" . $entry) . ">");
				} else {
					echo("<a href=/mp3/?file=" . urlencode($basedir) . urlencode($dir) . "/" . urlencode($entry) . ">");
				}
				echo("<img src='images/movie.png' border=0 alt='MP3'></a> ");
			} elseif ($publicstore) {
				echo("<a href='" . $baseurl . str_replace('%2F','/',str_replace('+',' ',urlencode($olddir . $symlink . "/" . $entry))) . "'>");
				echo("<img src='images/file.gif' border=0 alt='File'></a> ");
			} else {
				if ($symlink) {
					echo("<a href=view.php?file=" . urlencode($symbase . "/" . $entry) . ">");
				} else {
					echo("<a href=view.php?file=" . urlencode($basedir) . urlencode($dir) . "/" . urlencode($entry) . ">");
				}
				echo("<img src='images/file.gif' border=0 alt='File'></a> ");
			}
			if ($symlink) {
				$filepath = $symbase . "/" . $entry;
			} else {
				$filepath = $basedir . $dir . "/" . $entry;
			}
			echo("<font class='body'>" . str_replace('.',' ',$entry) . " <i>[" . get_filesize(@filesize($filepath)) . "]</i></font>");
			echo("<BR/>\n");
		}
	}
	
	if ($symlink) {
		$dirdetails = get_size($symbase);
	} else {
		$dirdetails = get_size($basedir . $dir);
	}
	
	$archivename = $server_name . str_replace(array('/', ' '), '_', $dir . $symlink);
	$candownload=0;
	
	$reason=0;
	if ($dirdetails[2] > 0 && $dirdetails[2] < return_bytes(ini_get('memory_limit')) && $dirdetails[2] < 128 * 1024 * 1024) { // PHP has a 128M Hard Limit, but check the ini's memory_limit to be nice
		if ($dirdetails[0] < 1000) {
			$candownload=1;
		} else {
			$reason=2;
		}
	} else {
		$reason=1;
	}
	
	echo("<center>");

	if($quality == 'mobile') {
		echo("<BR/><BR/><center><table width=90%>");
		echo("<TR><TD width=100%><img src='images/folder.gif'> <font size=-1 color=#666666>Folder</font></TD></TR>");
		echo("<TR><TD width=100%><img src='images/movie.png'> <font size=-1 color=#666666>Stream Video/Audio</font></TD></TR>");
		echo("<TR><TD width=100%><img src='images/file.gif'> <font size=-1 color=#666666>View/Open File</font></TD></TR>");
		echo("<TR><TD width=100%><img src='images/arrow.gif'> <font size=-1 color=#666666>Download File</font></TD>");
	} else {
		echo("<BR/><BR/><center><table width=300>");
		echo("<TR><TD width=44%><img src='images/folder.gif'> <font size=-1 color=#666666>Folder</font></TD>");
		echo("<TD width=56%><img src='images/movie.png'> <font size=-1 color=#666666>Stream Video/Audio</font></TD></TR><TR>");
		echo("<TD><img src='images/file.gif'> <font size=-1 color=#666666>View/Open File</font></TD>");
		echo("<TD><img src='images/arrow.gif'> <font size=-1 color=#666666>Download File</font></TD>");
	}

	echo '</TR></table><BR/><BR/>';
	
	if ($mp3count > 2) { 
		echo("<a href='mp3playlist.php?dir=" . urlencode($dir) . "&symlink=" . urlencode($symlink) . "'>Make a playlist of these MP3s!</a><BR/><BR/>");
	}
	
	if ($candownload) {
		if ($symlink) {
			echo("<a href='grabfolder.php?dir=$symbase&file=$archivename'>Grab this folder!");
			echo(" (" . get_filesize($dirdetails[2]) . ")");
			echo("</a>"); 
		} else {
			echo("<a href='grabfolder.php?dir=$basedir$dir&file=$archivename'>Grab this folder!");
			echo(" (" . get_filesize($dirdetails[2]) . ")");
			echo("</a>"); 
		}
	} else {
		if ($reason == 1) {
			if ($dirdetails[2] > 900000000) {
				echo("<font class=body>Folder is too large for direct download</font>");
				echo("<BR/>");
			} else {
				echo("<font class=body>Folder is too large for direct download</font>");
				echo("<BR/>");
			}
		} else {
			echo("<font class=body>Folder contains too many files for direct download</font>");
			echo("<BR/>");
		}
	}
	
	$url = '?';
	if(isset($_GET['pass']))
		$url .= 'pass=' . $_GET['pass'] . '&';
	if(isset($_GET['dir']))
		$url .= 'dir=' . urlencode($_GET['dir']) . '&';
	if(isset($_GET['symlink']))
		$url .= 'symlink=' . urlencode($_GET['symlink']) . '&';
	$url = substr($url,0,strlen($url)-1);
	
	if($quality == 'mobile') {
		echo '<BR/><font class="body">Using Mobile Video (3gp)</font>';
	} else {
		echo "<BR/><font class='body'>Video Quality: <i>" . ucfirst($quality) . ' (' . datarate($quality) . ')</i>';
		$out = '';
		if($quality!='tiny')
			$out .= "<A HREF=$url&quality=tiny>Tiny</a> / ";
		if($quality!='medium')
			$out .= "<A HREF=$url&quality=medium>Medium</a> / ";
		if($quality!='high')
			$out .= "<A HREF=$url&quality=high>High</a> / ";
		if($quality!='direct')
			$out .= "<A HREF=$url&quality=direct>Direct</a> / ";
		$out = substr($out,0,strlen($out)-3);
		echo '<BR/>Use ' . $out . ' Quality</font>';
	}

	echo("<BR/><BR/><BR/><font size=-1><a href='$PHP_SELF?logout=1'>Logout</a>");
}
?>
