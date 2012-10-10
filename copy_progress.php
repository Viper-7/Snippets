<?php
function copy_progress($source, $dest, $session_key = 'download', $context = NULL) {
	$existing_session = FALSE;

	if(!$context)
		$context = stream_context_create();
	
	$fp = fopen($source, 'r', FALSE, $context);
	$out = fopen($dest, 'w', FALSE, $context);
	if(!$fp) {
		trigger_error('Invalid URL supplied: ' . $source, E_USER_WARNING);
		return FALSE;
	}
	
	if(!$out) {
		trigger_error('Unable to open output file: ' . $dest, E_USER_WARNING);
		return FALSE;
	}

	$meta = stream_get_meta_data($fp);
	
	foreach($meta['wrapper_data'] as $header) {
		if(stripos($header, 'Content-Length') === 0) {
			$parts = explode(':', $header);
			
			if(count($parts) > 1) {
				$filesize = intval(trim($parts[1]));
			} else {
				trigger_error('Invalid Content-Length header in response: ' . $source, E_USER_WARNING);
			}
		}
	}
	
	if(!$filesize) {
		return FALSE;
	}
	
	if(!session_id()) {
		session_start();
		$existing_session = true;
	}
	
	$_SESSION[$session_key] = array(
		'URL' => $source,
		'TotalSize' => $filesize,
		'BytesRead' => 0,
		'Percent' => 0,
		'Complete' => FALSE,
		'Failed' => FALSE,
	);
	
	session_write_close();
	
	while(!feof($fp)) {
		$data = fread($fp, 256 * 1024); // Read in 256kb chunks
		$bytes = fwrite($out, $data);
		
		session_start();
		
		$_SESSION[$session_key]['BytesRead'] += $bytes;
		if($_SESSION[$session_key]['TotalSize'] == $_SESSION[$session_key]['BytesRead']) {
			$_SESSION[$session_key]['Complete'] = TRUE;
			$_SESSION[$session_key]['Percent'] = 100;
		} else {
			$_SESSION[$session_key]['Percent'] = $_SESSION[$session_key]['BytesRead'] / $_SESSION[$session_key]['TotalSize'] * 100;
		}
		
		session_write_close();
	}
	
	if($_SESSION[$session_key]['TotalSize'] != $_SESSION[$session_key]['BytesRead']) {
		$_SESSION[$session_key]['Failed'] = TRUE;
	}
	
	if($existing_session) {
		session_start();
	}
	
	return TRUE;
}
