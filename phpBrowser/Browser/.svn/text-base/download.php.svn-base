<?php
$secure=1;
require_once('checkaddr.php');

$file = str_replace("\'","'",$_GET['file']);

if(isset($_GET['imdbid'])) {
	$result = mysql_query("SELECT Filename, Part FROM imdbfiles WHERE imdbid=" . $_GET['imdbid']);
	if (mysql_num_rows($result) > 1) {
		list($name, $parts) = mysql_fetch_array(mysql_query("SELECT DISTINCT imdb.Name, MAX(imdbfiles.part) FROM imdb, imdbfiles WHERE imdb.id=imdbfiles.imdbid AND imdb.id=" . $_GET['imdbid'] . " GROUP BY imdb.Name"));
		echo "<BR/><BR/><BR/><CENTER>";
		while(list($filename, $part) = @mysql_fetch_array($result)) {
			$file = '/opt/filestore/Movies/' . $filename;
			echo "<A HREF='download.php?file=" . urlencode($file) . "'>$name - Part $part of $parts</A><BR/><BR/>";
		}
		die();
	} else {
		list($filename, $part) = @mysql_fetch_array($result);
		if ($part != 0) { die('Database query failed'); }
		$file = '/opt/filestore/Movies/' . $filename;
	}
}

$testfile = '-' . $file;
if(strpos($testfile,'/opt/') + strpos($testfile,'/mnt/') + strpos($testfile,'/tmp/') == 0) {
	die ('Fuck off hacker');
}

header('Content-disposition: attachment; filename="' . basename($file) . '"');
header("Cache-Control:");
header("Content-Type: application/octet-stream");

if ($online) 
	streamFile($file, 0, 850);
else
	streamFile($file, 0, 800);
?>
