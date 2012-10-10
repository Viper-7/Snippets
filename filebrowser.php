<?php
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

function listdir($dir = '.') {
	$contents = glob(join_path($dir, '*'));
	$dirs = array();
	$files = array();
	
	foreach($contents as $file) {
		$tmp = alt_stat($file);
		if($tmp['filetype']['is_dir']) {
			$dirs[] = $tmp;
		} else {
			$files[] = $tmp;
		}
	}
	return array_merge($dirs, $files);
}

function alt_stat($file) {
	$ss=@stat($file);
	if(!$ss) return false; //Couldnt stat file

	$ts=array(
		0140000=>'ssocket',
		0120000=>'llink',
		0100000=>'-file',
		0060000=>'bblock',
		0040000=>'ddir',
		0020000=>'cchar',
		0010000=>'pfifo'
	);

	$tmpsize = $ss['size'];
	$sizemod = 0;
	$modnames = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	while($tmpsize > 1024) {
		$tmpsize /= 1024;
		$sizemod++;
	}
	$hrsize = number_format($tmpsize,2) . $modnames[$sizemod];
	
	$p=$ss['mode'];
	$t=decoct($ss['mode'] & 0170000); // File Encoding Bit

	$str =(array_key_exists(octdec($t),$ts))?$ts[octdec($t)]{0}:'u';
	$str.=(($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
	$str.=(($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
	$str.=(($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
	$str.=(($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
	$str.=(($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
	$str.=(($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));

	$s=array(
		'perms'=>array(
			'umask'=>sprintf("%04o",@umask()),
			'human'=>$str,
			'octal1'=>sprintf("%o", ($ss['mode'] & 000777)),
			'octal2'=>sprintf("0%o", 0777 & $p),
			'decimal'=>sprintf("%04o", $p),
			'fileperms'=>@fileperms($file),
			'mode1'=>$p,
			'mode2'=>$ss['mode']
		),

		'owner'=>array(
			'fileowner'=>$ss['uid'],
			'filegroup'=>$ss['gid'],
			'owner'=>@posix_getpwuid($ss['uid']),
			'group'=>@posix_getgrgid($ss['gid'])
		),

		'file'=>array(
			'filename'=>$file,
			'realpath'=>@realpath($file),
			'dirname'=>@dirname($file),
			'realdirname'=>@dirname(@realpath($file)),
			'basename'=>@basename($file)
		),

		'filetype'=>array(
			'type'=>substr($ts[octdec($t)],1),
			'type_octal'=>sprintf("%07o", octdec($t)),
			'ext'=>pathinfo($file, PATHINFO_EXTENSION),
			'is_file'=> @is_file($file),
			'is_dir'=> @is_dir($file),
			'is_link'=> @is_link($file),
			'is_readable'=> @is_readable($file),
			'is_writable'=> @is_writable($file)
		),

		'device'=>array(
			'device'=>$ss['dev'], //Device
			'device_number'=>$ss['rdev'], //Device number, if device.
			'inode'=>$ss['ino'], //File serial number
			'link_count'=>$ss['nlink'], //link count
			'link_to'=>($s['type']=='link') ? @readlink($file) : ''
		),

		'size'=>array(
			'size'=>$ss['size'], //Size of file, in bytes.
			'hrsize'=>$hrsize,
			'blocks'=>$ss['blocks'], //Number 512-byte blocks allocated
			'block_size'=> $ss['blksize'] //Optimal block size for I/O.
		),

		'time'=>array(
			'mtime'=>$ss['mtime'], //Time of last modification
			'atime'=>$ss['atime'], //Time of last access.
			'ctime'=>$ss['ctime'], //Time of last status change
			'accessed'=>@date('Y-m-d H:i:s',$ss['atime']),
			'modified'=>@date('Y-m-d H:i:s',$ss['mtime']),
			'created'=>@date('Y-m-d H:i:s',$ss['ctime'])
		),
	);

	return $s;
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>File Listing</title>
	<style type="text/css">
		tr.even {
			background-color: #eaeaea;
		}
		
		table {
			margin: 0 auto;
		}
	</style>
</head>
<body>
	<table width="1000" cellspacing="0">
		<tr>
			<th>Filename</th>
			<th>Type</th>
			<th>Size</th>
			<th>Owner</th>
			<th>Permissions</th>
			<th>Realpath</th>
			<th>Last Modified</th>
			<th>Readable</th>
			<th>Writable</th>
		</tr>
		<?php
			$dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
			$files = listdir($dir);

			$i=0;
			foreach($files as $file) {
				$i++;
				if($i & 1) {
					echo '<tr class="even">';
				} else { 
					echo '<tr class="odd">';
				}
				echo '<td>';
				if($file['filetype']['is_dir']) {
					echo '<a href="?dir=' . $file['file']['realpath'] . '">' . $file['file']['basename'] . '</a>';
				} else {
					echo $file['file']['basename'];
				}
				echo '</td>';
				echo '<td>' . ucfirst($file['filetype']['type']) . '</td>';
				echo '<td>' . $file['size']['hrsize'] . '</td>';
				echo '<td>' . $file['owner']['owner']['name'] . ':' . $file['owner']['group']['name'] . '</td>';
				echo '<td>' . $file['perms']['human'] . '</td>';
				echo '<td>' . $file['file']['realdirname'] . '</td>';
				echo '<td>' . $file['time']['modified'] . '</td>';
				echo '<td>' . ($file['filetype']['is_readable']?'Yes':'No') . '</td>';
				echo '<td>' . ($file['filetype']['is_writable']?'Yes':'No') . '</td>';
				echo '</tr>';
			}
		?> 
	</table>
</body>
</html>