<pre><?php
/**
* Gets an array of paths to files in a folder / subfolders by mime type
*
* $files = getFilesByMime('/var/www/images', array('image/', 'video/'), 2);
*
* @param string Path to search in
* @param string/array Mime type or partial mime type to search for
* @param integer Recursion depth
* @param string/array
**/
function getFilesByMime($folder, $mime_types, $depth = 0, $extensions = NULL) {
	$found = array();
	
	if(!is_array($mime_types))
		$mime_types = array($mime_types);
	
	if($extensions) {
		if(is_array($extensions)) 
			$extensions = implode(',', $extensions);
		
		$extensions = '.{' . $extensions . '}';
	}
	
	if($depth) foreach(range(1, $depth) as $i) {
		$parts[] = str_repeat('*/', $i);
	}
	
	$recursion = '{' . implode(',', $parts) . '}';
	
	$files = glob("{$folder}/{$recursion}*{$extensions}", GLOB_BRACE);
	
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	
	foreach($files as $path) {
		if(!is_readable($path))
			continue;
		
		$mime = finfo_file($finfo, $path);
		
		foreach($mime_types as $allowed) {
			if(strpos($mime, $allowed) !== FALSE) {
				$found[] = $path;
			}
		}
	}
	
	return $found;
}

