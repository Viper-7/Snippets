<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head><title>IRC Log</title><meta http-equiv="refresh" content="60"></head>
<body bgcolor="#000000" text="#6666CC">
<?php
	include('irc2html.php');
	if(isset($_GET['channel'])) {
		if(isset($_GET['network'])) {
			IRC2HTML::parseFile('/mnt/madcat/h/Documents and Settings/Viper-7/Application Data/NoNameScript/logs/',1000,$_GET['network'],$_GET['channel']);
		} else {
			IRC2HTML::parseFile('/mnt/madcat/h/Documents and Settings/Viper-7/Application Data/NoNameScript/logs/',1000,'freenode',$_GET['channel']);
		}
	} else {
		IRC2HTML::parseFile('/mnt/madcat/h/Documents and Settings/Viper-7/Application Data/NoNameScript/logs/freenode/##php.log');
	}
?>
</body></html>