<?php
	session_start();
	if (isset($_GET['user'])) { $username = $_GET['user']; } 			// Comment these 2 lines to disable GET url logins 
	if (isset($_GET['pass'])) { $password = $_GET['pass']; } 			// ie (http://your-site/?user=admin&pass=test)
	if (isset($_POST['user'])) { $username = $_POST['user']; }			//
	if (isset($_POST['pass'])) { $password = $_POST['pass']; }			// Or remove these lines and populate $username and 
	if (isset($_SESSION['username'])) { $username = $_SESSION['username']; }	// $password from your own login system here
	if (isset($_SESSION['password'])) { $password = $_SESSION['password']; }	//
?>
<CENTER><IFRAME SRC="index.php?user=<?=$username?>&pass=<?=$password?>" HEIGHT="100%" WIDTH="760" FRAMEBORDER="0"></IFRAME></CENTER>