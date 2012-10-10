<?php
	// Root directory of the server
	$basedir = '/opt/uploads';
	
	// Content type to send with files (set to blank to read from file)
	$content_type = 'application/octet-stream';
	
	// Title for the page
	$title = "My File Storage";
	
	// Password for the page
	$pass = 'securepass';
	
	

	session_start();
	if(isset($_POST['pass'])) {
		if($pass == trim($_POST['pass'])) {
			$_SESSION['passset'] = true;
		}
	}
	
	if(empty($_SESSION['passset'])) {
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php echo $title; ?></title>
</head>
<body>
	<div style="margin: 0 auto; width: 300px">
		<p>
			<form method="post" action="">
				<input type="password" name="pass" size="20"> &nbsp;
				
				<?php if(isset($_REQUEST['dir'])) { ?>
					<input type="hidden" name="dir" value="<?php echo $_REQUEST['dir'] ?>">
				<?php } ?>
				
				<?php if(isset($_REQUEST['file'])) { ?>
					<input type="hidden" name="file" value="<?php echo $_REQUEST['file'] ?>">
				<?php } ?>
				
				<input type="submit" value="Login">
			</form>
		</p>
	</div>
</body>
</html>
<?php
		die();
	}
	
	// We're done with the session, free the lock
	session_write_close();

	// Process Dir
	if(isset($_REQUEST['dir'])) {
		// Find the requested dir on the filesystem
		$dirpath = realpath(join_path($basedir,isset($_REQUEST['dir']) ? $_REQUEST['dir'] : ''));
		
		// Make sure it's inside the basedir
		if(substr($dirpath, 0, strlen($basedir)) != $basedir) die('Fuck off hacker');
		
		// Build the relative path to work with
		$requestdir = substr($dirpath, strlen($basedir)+1);

	// Process File
	} elseif (isset($_REQUEST['file'])) {
		// Find the requested file on the filesystem
		$filepath = realpath(join_path($basedir,isset($_REQUEST['file']) ? $_REQUEST['file'] : ''));

		// Make sure it's inside the basedir
		if(substr($filepath, 0, strlen($basedir)) != $basedir) die('Fuck off hacker');

		// Build the relative path to work with
		$requestdir = substr($filepath, strlen($basedir)+1);

		// Send the content-type header if set
		if(!empty($content_type))
			header('Content-Type: ' . $content_type);

		// Send the filename to download the file as
		header('Content-Disposition: attachment; filename="' . basename($_REQUEST['file']) . '"');

		// Output the file
		readfile(join_path($basedir, $requestdir));

		// Don't process the rest of the script
		die();

	// Show base folder
	} else {
		$dirpath = $basedir;
		$requestdir = '';
	}

	// Create the folder if requested
	if(!empty($_POST['createfolder'])) {
		@mkdir(join_path($dirpath, $_POST['createfolder']));
	}

	// Process the file upload if attached
	if(!empty($_FILES['upload']['name'])) {
		move_uploaded_file($_FILES['upload']['tmp_name'], join_path($dirpath, basename($_FILES['upload']['name'])));
	}

	// Read all the entries in the requested path
	$dirlisting = glob($dirpath . '/*');

	// Build separate arrays of dirs and files
	$dirs = array(); $files = array();
	foreach($dirlisting as $elem) {
		if(is_dir($elem))
			$dirs[] = $elem;
		else
			$files[] = $elem;
	}

	function join_path() {
		$args = func_get_args();
		$absolute = FALSE;

		if($args[0][0] == DIRECTORY_SEPARATOR) { $absolute = TRUE; }

		foreach($args as $arg) {
			$targs[] = trim($arg,DIRECTORY_SEPARATOR);
		}

		$path = implode(DIRECTORY_SEPARATOR, $targs);
		if($absolute) $path = DIRECTORY_SEPARATOR . $path;

		return $path;
	}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php echo $title; ?></title>
</head>
<body>
	<div style="margin: 0 auto; width: 600px">
		<p>
		<?php
			if(!empty($requestdir))
				echo '<img src="folder.gif"> <a href="?dir=' . urlencode(substr(realpath(join_path($dirpath, '..')),strlen($basedir)+1)) . '">..</a><br>';
			
			foreach($dirs as $dir)
				echo '<img src="folder.gif"> <a href="?dir=' . urlencode(join_path($requestdir, basename($dir))) . '">' . basename($dir) . '</a><br>';
			
			foreach($files as $file) {
				echo '<img src="file.gif"> <a href="?file=' . urlencode(join_path($requestdir, basename($file))) . '">' . basename($file) . '</a>';
				$tmpsize = filesize($file);
				$sizemod = 0;
				$modnames = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
				while($tmpsize > 1024) {
					$tmpsize /= 1024;
					$sizemod++;
				}
				$hrsize = number_format($tmpsize,2) . $modnames[$sizemod];
				echo '&nbsp; &nbsp; [' . $hrsize . ']<br>';
			}
		?>
		</p>
	</div>
	<br>
	<div style="margin: 0 auto; width: 400px">
		<p>
			<form action="" method="post" enctype="multipart/form-data">
				<input type="hidden" name="dir" value="<?php echo $requestdir; ?>">
				<label for="upload">Upload File:</label><br>
				<input type="file" name="upload" id="upload" size="30"> &nbsp;
				<input type="submit" value="Upload!">
				<br><br>
				<label for="createfolder">Create Folder:</label><br>
				<input type="text" name="createfolder" id="createfolder" size="30"> &nbsp;
				<input type="submit" value="Create!">
			</form>
		</p>
	</div>
</body>
</html>
