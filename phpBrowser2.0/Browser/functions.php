<?php
	function checkprocess ($search) {
		$running = false;
		$pid = 0;
		$out = shell_exec('ps -o pid,args ax | grep ' . $search . ' 2>/dev/null');
		$out = split("\n", $out);
		foreach($out as $pid) {
			if (strpos($pid,'grep'))
				continue;
			$pid = trim($pid);
			$pid = trim(substr($pid,0,strpos($pid, ' ')));

			if (strlen($pid) > 0)
				$running = true;
		}
		return $running;
	}
	
	function datarate($quality) {
		$datarate = '';
		switch($quality) {
			case 'tiny':
				$datarate = '20k/s'; break;
			case 'medium':
				$datarate = '50k/s'; break;
			case 'high':
				$datarate = '100k/s'; break;
			case 'direct':
				$datarate = '?k/s'; break;
		}
		return $datarate;
	}

	function &array_shift_reference(&$array) {
		if (count($array) > 0)
		{
			$key = key($array);
			$first =& $array[$key];
		} else {
			$first = null;
		}
		array_shift($array);
		return $first;
	}
	
	function get_size($dir) {
	       $speicher = 0;
	       $dateien = 0;
	       $verz = 0;
	       if ($handle = @opendir($dir)) {
	           while ($file = readdir($handle)) {
	               if($file != "." && $file != ".." && $file != "Books") {
	                   if(@is_dir($dir."/".$file)) {
				$wert = get_size($dir."/".$file);
				$speicher +=  $wert[2];
				$dateien +=  $wert[0];
				$verz +=  $wert[1];
				$verz++;
				if ($speicher > 900000000) {
					break;
				}
	                   } else {
				$speicher += @filesize($dir."/".$file);
				$dateien++;
	                   }
	                   if ($dateien > 1000 || $verz > 1000) { break; }
	               }
	           }
	       closedir($handle);
	       }
	       $zurueck[0] = $dateien;
	       $zurueck[1] = $verz;
	       $zurueck[2] = $speicher;
	       return $zurueck;
	}
	
	function return_bytes($val) {
	    $val = trim($val);
	    $last = strtolower($val[strlen($val)-1]);
	    switch($last) {
	        // The 'G' modifier is available since PHP 5.1.0
	        case 't':
	            $val *= 1024;
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }
	
	    return $val;
	}

	function avlength ($filename) {
		$stats = shell_exec("avidentify " . escapeshellarg($filename) . ' 2>/dev/null');
		$stats = trim(array_shift(split("start",array_pop(split('Duration:',$stats)))));
		$ta = split(':',$stats);
		$orghrs = intval($ta[0]);
		$orgmins = intval($ta[1]);
		$orgsecs = intval(substr($ta[2],0,strpos($ta[2],'.')));
		$orgtime = intval($orgsecs + ($orgmins * 60) + ($orghrs * 60 * 60));
		return $orgtime;
	}
	
	function get_filesize ($dsize) {
		if (strlen($dsize) <= 9 && strlen($dsize) >= 7) {
			$dsize = number_format($dsize / 1048576,1);
			return $dsize . "mb";
		} elseif (strlen($dsize) >= 10) {
			$dsize = number_format($dsize / 1073741824,1);
			return $dsize . "gb";
		} else {
			$dsize = number_format($dsize / 1024,1);
			return $dsize . "kb";
		}
	}
	function date_diff($start, $end = '') {
		$start = strtotime($start);
		if($end != '') 
			$end = strtotime($end);
		else
			$end = time();
		
		$diff = $end - $start;
		return $diff;
	}
	
	function format_date ($original='', $format="%m/%d/%Y") {
		$format = ($format=='date' ? "%d-%m-%Y" : $format);
		$format = ($format=='datetime' ? "%r - %d/%m/%y" : $format);
		$format = ($format=='rss-datetime' ? "%a, %d %b %Y %H:%M:%S %z" : $format);
		$format = ($format=='mysql-date' ? "%Y-%m-%d" : $format);
		$format = ($format=='mysql-datetime' ? "%Y-%m-%d %H:%M:%S" : $format);
		return (!empty($original) ? gmstrftime($format, $original) : "" );
	} 

	function statusfromlog($log, $firstparam) {	
		$seek = 4096;
		$filesize = filesize($log);
		$fp = fopen($log, 'r');
		fseek($fp, $filesize-$seek);
		$log = fread($fp,$seek);
		$log = split($firstparam, $log);
		while (count($log) < 2 && !feof($fp) && $seek < $filesize && $seek < 16*1024*1024) {
			$seek += 4096;
			fseek($fp, filesize($log)-$seek);
			$log = fread($fp,$seek);
			$log = split($firstparam, $log);
		}
		fclose($fp);
		while (count($log) > 1)
			array_shift($log);
	
		$log = $firstparam . trim($log[0]);
		return $log;
	}
	
	// Stream a file as its being created
	// Author: Dale Horton (www.viper-7.com/trac)
	// fopen/fread will only return a file up to the size it was when you started the read
	// Keep reading until we really get to the end or we catch up to the server
	//
	// Usage: streamFile(Filename to read, Starting position in Bytes)
	//
	function streamFile($filename, $start = 0) {
		
		// Open the file and setup our first pass
		$cur = $start;
		$file = fopen($filename, 'rb');
		$end = filesize($filename);
		$oldchunk = '';
		
		// Loop until we've read up to the filesize of when we started
		while ($cur < $end-8) {
			$out = '';

			// Seek to the supplied start value or to the end of the last pass
			fseek($file,$cur,0);
			
			// Read a 16kb chunk from the current location
			$out = @fread($file,1024*16);
			
			// If theres less than 16kb remaining in the file, just read that much
			if ($out == '' && $end-$cur < 1024*16)
				$out = @fread($file,$end-$cur);
			
			$len = mb_strlen($out);

			// If fopen has returned an EOF flag, we don't want the last byte (the EOF marker)
			if (feof($file))
				$len = $len - 8;
			
			$oldchunk = mb_substr($out,$len,8);
			
			// Trim the output to the size we expect
			$out = mb_substr($out,0,$len);
			
			// Output the chunk
			print $out;

			// Increment the current position by the amount of data read
			$cur += $len;
		}
		
		sleep(1);
		
		// Compare the filesize to when we started
		clearstatcache();
		if (filesize($filename) > $end) {
			// If the file is has grown, it's still being generated, so setup for another pass
			$end = filesize($filename);
			
			// Throw away the old file handle and cleanup all traces of it
			fclose($file);
			unset($file);
			clearstatcache();
			
			// Start the next pass from the current position
			streamFile($filename, $cur);
		} else {
			print $oldchunk;
		}
	}
?>