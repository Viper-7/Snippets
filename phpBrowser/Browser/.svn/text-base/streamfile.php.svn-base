<?php
	// Stream a file as its being created
	// Author: Dale Horton (www.viper-7.com/trac)
	// fopen/fread will only return a file up to the size it was when you started the read
	// Keep reading until we really get to the end or we catch up to the server
	//
	// Usage: streamFile(Filename to read, Starting position in Bytes)
	//
	function streamFile($filename, $start = 0, $speedlimit = 0) {
		
		// Close any open session handles
		if(isset($_SESSION['username'])){session_write_close();}
		
		// Open the file and setup our first pass
		$cur = $start;
		if($speedlimit)
			$slicesize=1024*($speedlimit + ($speedlimit * 0.0098));
		else
			$slicesize=1024*16;	// 16kb Slices
		
		$file = fopen($filename, 'r');
		$end = filesize($filename);

		if ($end <= 1) {
			$time = microtime(true);
			$out = '';

			// Seek to the supplied start value or to the end of the last pass
			fseek($file,$cur,SEEK_SET);
			
			// Read a 16kb chunk from the current location
			while(!feof($file)) {
				print @fread($file,$slicesize);

				// If a speed limit is set
				if($speedlimit) {
					// Sleep for 1 seconds
					$diff = microtime(true) - $time;
					if ($diff <= 0 || $diff >= 800000) $diff = 0;
					usleep(1000000 - $diff);
				}
			}
		} else {
			$oldchunk = '';
			
			// Loop until we've read up to the filesize of when we started
			while ($cur < $end-8) {
				$time = microtime(true);
				$out = '';
				
				// Seek to the supplied start value or to the end of the last pass
				fseek($file,$cur,SEEK_SET);
				
				// If theres less than 16kb remaining in the file, just read that much
				if ($out == '' && $end-$cur < $slicesize)
					$out = @fread($file,$end-$cur);
				else // Read a 16kb chunk from the current location
					$out = @fread($file,$slicesize);
				
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
				
				// If a speed limit is set
				if($speedlimit) {
					// Sleep for 1 seconds
					$diff = microtime(true) - $time;
					if ($diff <= 0 || $diff >= 800000) $diff = 0;
					usleep(1000000 - $diff);
				}
			}

			sleep(1);
			
			// Compare the filesize to when we started
			clearstatcache();

			// If the file is has grown, it's still being generated, so setup for another pass
			if (filesize($filename) > $end) {
				// Throw away the old file handle and cleanup all traces of it
				fclose($file);
				clearstatcache();
				
				// Start the next pass from the current position
				streamFile($filename, $cur, $speedlimit);
			} else {
				print $oldchunk;
			}
		}
	}
?>