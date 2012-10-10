<?php
require('config.php');

function list_dir($dir) {
	echo "<TABLE WIDTH=50% BORDER=0><TR><TH>Type</TH><TH>Filename</TH><TH>Size</TH><TH>MD5</TH></TR>";
	
	if (is_dir($dir)) {
		$arr = scandir($dir);
		foreach ($arr as $item) {
			if (is_dir($dir . '/' . $item)) {
				if ($item != "." && $item != "..") {
					echo "<TR><TD>DIR</TD><TD>$item</TD></TR>";
				}
			} else {
				$file = $dir . '/' . $item;
				echo "<TR><TD>FILE</TD><TD>$item</TD><TD>" . get_filesize(filesize($file)) . "</TD><TD>" . md5_file($file) . "</TD></TR>";
			}
		}
	} else {
		return false;	
	}
	
	echo "</TABLE>";
}

list_dir($_GET['folder']);

?>