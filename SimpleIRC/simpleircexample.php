<pre>
<?php	
	// SimpleIRC Example Usage

	include('simpleirc.php');
	
	$si = new simpleirc();
	
	$si->connect('irc.freenode.net', 'v7testBot') or die('Failed to connect to IRC server');
	
	$si->send('#v7test2', 'hello');
	
	$uinfo = $si->whois('Viper-7');
	print_r($uinfo);
	
	$userlist = $si->getchanlist('##php');
	print_r($userlist);
	
	$si->disconnect();
?>
</pre>
