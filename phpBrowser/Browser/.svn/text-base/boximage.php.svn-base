<?php
	$url = $_GET['url'];
	if(strpos(0,'/etc/',$url) || strpos(0,'/proc/',$url) || strpos(0,'/sys/',$url) || strpos(0,'/var/',$url))
		die();
	$file = file_get_contents($url);

	header("Content-type: image/jpeg\r\n");
	header("Content-Transfer-Encoding: binary\r\n");
	
	echo $file;
?>