<?php
	if ($mkpasswd) {
		if (isset($_POST['mkpasswd'])) {
			die(md5($password));
		}
	}
	
	if ($_GET['logout']) {
		unset($username);
		unset($password);
		unset($_SESSION['username']);
		unset($_SESSION['password']);
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
			if (!isset($_SESSION['username'])) {
				$_SESSION['username'] = $username;
				$_SESSION['password'] = $password;
			}
		}
	}
	
	if ($loggedin==0) { 
		unset($username); 
		unset($password); 
		$needlogin=1;
		if ($_GET['file']) {
			$file = $_GET['file'];
			$dir = dirname($file);
			foreach ($users as $user) {
				if (strpos('-' . $user[2], $dir)) {
					$basedir = $user[2];
					$secure = $user[4];
				}
			}
		} elseif ($_GET['dir']) {
			$dir = $_GET['dir'];
			if (substr($dir,0,1) != '/') {
				$dir = '/' . $dir;
			}
			foreach ($users as $user) {
				if (file_exists($user[2] . $dir)) {
					chdir ($user[2] . $dir);
					$pwd = getcwd();
					if (strpos('-' . $pwd, $user[2] . $dir)) {
						$username = $user[0];
						$password = $_GET['pass'];
						$basedir = $user[2];
						$secure = $user[4];
						if (md5($_GET['pass']) == $user[1]) { $needlogin=0; }
					}
				}
			}
		}
	}

	if (!isset($_SESSION['username']) && isset($username)) {
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $password;
	}

	if (!$baseurl) {
		$publicstore=0;
	}
?>
