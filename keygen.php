<?php

$range = new RangeFinder();			// Create our rangefinder object (this loads the charlist and min/max chars into memory)
$file = new LockedFile('file.txt');		// Open our output file using the LockedFile class (also locks the file)
$key = $file->read();				// Read the entire contents of the file
$key = $range->incstr($key);			// Increment the value that we read from the file
$file->overwrite($key);				// Empty the file and write the string back to it
echo $key;					// Output the string to the client
$file->close();					// Close the file handle and unlock the file


class RangeFinder {
	private $plainlist;			// Stores the array of characters we'll use for the first & last characters
	private $charlist;			// Stores the array of characters we'll use - This can include extra characters that will only be in the middle of the string, 
						// but it MUST start & end with the same characters as plainlist
	private $minchar;			// Stores the first character in the set which we use when adding a char
	private $maxchar;			// Stores the last character in the set which we use to check if we need to increment the next char

	public function __construct() {
		$this->charlist 	= array_merge(range('a', 'z'), array('!','/','$'), range('A', 'Z'));
		$this->plainlist 	= array_merge(range('a', 'z'), range('A', 'Z'));
		$this->minchar 		= $this->charlist[0];
		$this->maxchar 		= end($this->charlist);
	}

	// incstr() takes a string, increments the rightmost character, or if its already at max, 
	// ratchets down through the characters resetting them to the first character and incrementing the next one
	public function incstr($str) {
		if($str == '') return 'a';

		$chararr = str_split($str);

		for($x = count($chararr)-1;$x >= 0; $x--) {
			if($chararr[$x] != $this->maxchar) {
				if($x != 0 && $x != count($chararr)-1) {
					$chararr[$x] = $this->charlist[array_search($chararr[$x], $this->charlist)+1];
				} else {
					$chararr[$x] = $this->plainlist[array_search($chararr[$x], $this->plainlist)+1];
				}
				return implode($chararr);
			} else {
				$chararr[$x] = $this->minchar;
			}
		}

		// If we reach this point, every char was at max. They've all been reset to minimum, so we need to add another char to the string
		array_unshift($chararr, $this->minchar);
		return implode($chararr);
	}
}

class LockedFile {
	private $file;				// Stores the file handle
	private $filename;			// Stores the filename to we can check it's size later
	private $tempdir;			// Stores the system's temporary directory where we'll store the lock file
	private $lockfile;			// Stores the full path & name of the lock file


	// LockedFile constructor. Takes a filename, and optionally the maximum time to wait between lock attempts $waitTime
	// and the number of seconds to try to lock a file before expiring $expireTime
	public function __construct($fname, $waitTime = 250, $expireTime = 10) {
		$this->filename = $fname;
		$this->tempdir = $this->gettempdir();
		$this->lockfile = $this->tempdir . basename($fname) . '.lck';
			
		$i=0;
		clearstatcache();

		if($tmpptr = @fopen($this->lockfile, 'x+')) { fwrite($tmpptr, '1'); fclose($tmpptr); }

		while(!$tmpptr) {
			$i += ($sleep = rand(100,$waitTime));
			usleep($sleep);
			clearstatcache();
			if (($i/1000000) > $expireTime) die('ERROR:CANNOT LOCK DATA FILE');
			$tmpptr = @fopen($this->lockfile, 'x+');
			if($tmpptr) { fwrite($tmpptr, '1'); fclose($tmpptr); }
		}

		if(is_file($this->filename)) {
		    $this->file = fopen($this->filename, 'r+');
		} else {
		    $this->file = fopen($this->filename, 'w+');
		}

		if(!is_resource($this->file)) { unlink($this->lockfile); die('ERROR:NO DATA FILE'); }
	}
    
	public function __destruct() {
		$this->close();
	}

	private function gettempdir() {
		$tempfile = tempnam('/tmp', 'php');
		$tempdir = dirname($tempfile) . DIRECTORY_SEPARATOR;
		unlink($tempfile);
		return $tempdir;
	}
	
	// read() returns the entire contents of the file, or '' if the file is empty
	public function read($defaultvalue = '') {
		clearstatcache();
		if(@filesize($this->filename) == 0) {
		    return $defaultvalue;
		}

		rewind($this->file);
		return trim(fread($this->file, filesize($this->filename)));
	}
    
	// write($contents) writes $contents to the file at the current pointer location
	public function write($contents) {
		fwrite($this->file, $contents);
	}
	
	// overwrite($contents) truncates the file to 0 bytes, rewinds the file pointer, and writes $contents to the file 
	public function overwrite($contents) {
		rewind($this->file);
		ftruncate($this->file, 0);
		$this->write($contents);
	}
	
	// close() closes the file handle and removes the lock file
	public function close() {
		@fclose($this->file);
		if(is_file($this->lockfile)) { unlink($this->lockfile); }
	}
}