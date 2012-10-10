<?php
	if ($mkpasswd) {
		if (isset($_POST['mkpasswd'])) {
			die(md5($password));
		}
	}
	
	if ($_GET['logout']) {
		unset($username);
		unset($password);
		if(isset($_SESSION['username'])) {
			session_destroy();
		}
		header('Location: index.php');
		die();
	}
	
	$loggedin=0;
	foreach ($users as $user) {
		if ($user[0]==$username && $user[1]==md5($password)) {
			$basedir = $user[2];
			$baseurl = $user[3];
			$secure = $user[4];
			$server_name = $user[5];
			$loggedin=1;
			if (!isset($_GET['file']) && !isset($_GET['ticket'])) {
				session_start();
			}
			$_SESSION['username'] = $username;
			$_SESSION['password'] = $password;
		}
	}
	
	if (!$basedir && $loggedin==0) { 
		unset($username); 
		unset($password); 
		$needlogin=1;
		if(isset($_GET['dir'])) $dir = $_GET['dir'];
		if ($_GET['file']) {
			$file = $_GET['file'];
			$dir = dirname($file);
			foreach ($users as $user) {
				if (strpos('-' . $user[2], $dir)) {
					$basedir = $user[2];
					$secure = $user[4];
					$server_name = $user[5];
				}
			}
		} elseif ($dir) {
			$len = strlen($dir);
			if (substr($dir,$len-1,1)=='/')
				$dir = substr($dir,0,$len-1);
			if (substr($dir,$len-3,3)=='%2F')
				$dir = substr($dir,0,$len-3);
			
			if (substr($dir,0,1) != '/' && substr($dir,0,3)!='%2F') {
				$dir = '/' . $dir;
			}
			foreach ($users as $user) {
				if (file_exists($user[2] . $dir)) {
					if ($user[4]) { // Secure?
						if (md5($_GET['pass']) == $user[1]) { 
							chdir($user[2] . $dir);
							$pwd = getcwd();
							$pwd = substr($pwd,0,strpos($pwd,$dir));
							$needlogin=0;
							$username = $user[0];
							$password = $_GET['pass'];
							$basedir = $pwd;
							$secure = $user[4];
							$server_name = $user[5];
						}
					} else {
						chdir($user[2] . $dir);
						$pwd = getcwd();
						$pwd = substr($pwd,0,strpos($pwd,$dir));
						$needlogin=0;
						$username = $user[0];
						$basedir = $pwd;
						$secure = 0;
						$server_name = $user[5];
					}
				}
			}
		}
	}
	
	if (!$basedir || $basedir == '/') {
		$needlogin=1;
		if(isset($_SESSION['username'])) {
			session_destroy();
		}
	}

	if (!isset($_SESSION['username']) && isset($username)) {
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $password;
	}
?>
