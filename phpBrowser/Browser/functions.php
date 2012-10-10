<?php
	require('streamfile.php');
	
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
				$datarate = '25k/s'; break;
			case 'medium':
				$datarate = '50k/s'; break;
			case 'high':
				$datarate = '125k/s'; break;
			case 'direct':
				$datarate = '>300k/s'; break;
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

	function microtime_point()
	{
		list($usec, $sec) = explode(" ", microtime());
		return (substr($usec,2,5) + ($sec * 100000));
	}

	//Return the unix timestamp + microseconds
	function micro_time()
	{
		$timearray = explode(" ", microtime());
		return ($timearray[1] + $timearray[0]);
	}
	
	function shuffle_with_keys(&$array, $limit=10000) {
		$aux = array();
		$keys = array_keys($array);
		shuffle($keys);
		$count=0;
		foreach($keys as $key) {
			if($count>$limit) break; $count++;
			$aux[$key] = $array[$key];
			unset($array[$key]);
		}
		$array = $aux;
	}
?>