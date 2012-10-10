<?php
class Locker
{
	const FILELOCK = 1;
	const APCLOCK = 2;
	const MEMCACHELOCK = 4;
	
	const LOCK_EXCLUSIVE = 1;
	const LOCK_SHARED = 2;
	const LOCK_RELEASE = 4;
	
	private $instance;
	
	public function __construct($key, $type=self::FILELOCK, $mode=self::LOCK_SHARED, $timeout=10, $poll=250)
	{
		switch($type)
		{
			case FILELOCK:
				$this->instance = new Locker_FileLock($key, $mode, $timeout, $poll);
				break;
			case APCLOCK:
				$this->instance = new Locker_APCLock($key, $mode, $timeout, $poll);
				break;
			case MEMCACHELOCK:
				$this->instance = new Locker_MemcacheLock($key, $mode, $timeout, $poll);
				break;
		}
	}
	
	public function __get($var)
	{
		return $this->instance->$var;
	}
	
	public function __set($var, $value)
	{
		$this->instance->$var = $value;
	}
	
	public function __call($func, $args)
	{
		return call_user_func_array(array($this->instance, $func), $args);
	}
}

class Locker_Timeout_Exception extends Exception {}
class Locker_Permission_Exception extends Exception {}

class Locker_FileLock_Timeout_Exception extends Locker_Timeout_Exception {}
class Locker_FileLock_Permission_Exception extends Locker_Permission_Exception {}

class Locker_APCLock_Timeout_Exception extends Locker_Timeout_Exception {}

class Locker_MemcacheLock_Timeout_Exception extends Locker_Timeout_Exception {}

class Locker_APCLock {
	private $lockType = self::Lock_Exclusive;	// Type of lock (Exclusive or Shared)
	private $lockExpiry = 172800;				// Seconds before forcing expiration of a lock (default is 48 Hours)
	private $pid;								// PID of the current process
	private $key;								// Key (filename) for this lock
	private $sharedKey;							// Key (filename) for this lock
	
	const Lock_Exclusive = 1;
	const Lock_Shared = 2;
	const Lock_Release = 4;
	
	/*
	*	Locker_FileLock()
	*
	*	@param string Filename
	*	@param integer Seconds before timing out on fetching lock
	*	@param integer Time to wait between lock attempts (us)
	*/
	public function __construct($key, $type = Locker_FileLock::Lock_Exclusive, $timeout = 10, $pollInterval = 250)
	{
		$this->key = $key;
		$this->sharedKey = ;
		$this->pid = getmypid();
		$this->tempDir = sys_get_temp_dir();
		$now = time();
		
		switch($type)
		{
			case self::Lock_Exclusive:
				$i=0;
				while(TRUE) {
					if($i / 1000000 > $timeout)
						throw new Locker_FileLock_Timeout_Exception('Failed to obtain lock');

					clearstatcache();
					if(!file_exists($this->lockFile))
					{
						$tmpptr = @fopen($this->lockFile, 'x');
						if(!$tmpptr) continue;
						fwrite($tmpptr, '1'); 
						fclose($tmpptr); 
						
						// Wait for shared locks
						while(TRUE) {
							if($i / 1000000 > $timeout)
								throw new Locker_FileLock_Timeout_Exception('Failed to obtain lock');
							
							$files = glob($this->lockFile . '.*');
							if(empty($files)) break;
							$current = FALSE;
							foreach($files as $file)
							{
								if($now - filemtime($file) < $this->lockExpiry)
								{
									$current = TRUE;
									break;
								} else {
									@unlink($file);
								}
							}
							if(!$current) break;
							
							$i += $sleep = rand(100, $pollInterval);
							usleep($sleep);
							clearstatcache();
						}
						
						break 2;
					}
					
					$i += $sleep = rand(100, $pollInterval);
					usleep($sleep);
				}
				
			case self::Lock_Shared:
				$i=0;
				while(TRUE) {
					if($i / 1000000 > $timeout)
						throw new Locker_FileLock_Timeout_Exception('Failed to obtain lock');

					clearstatcache();
					if(!file_exists($this->lockFile))
					{
						$tmpptr = fopen($this->sharedLockFile, 'w');
						if(!$tmpptr) continue;
						fwrite($tmpptr, '2'); 
						fclose($tmpptr);
						break 2;
					}
					
					$i += $sleep = rand(100, $pollInterval);
					usleep($sleep);
				}
			
			case self::Lock_Release:
				$this->__destruct();
				$this->lockType = 0;
				break;
		}
	}
	
	public function __destruct() {
		if($this->lockType & (self::Lock_Release | self::Lock_Shared) && is_file($this->sharedLockFile))
		{
			if(!@unlink($this->sharedLockFile))
				throw new Locker_FileLock_Permission_Exception('Permission denied on ' . $this->sharedLockFile);
		}
		
		if($this->lockType & (self::Lock_Release | self::Lock_Exclusive) && is_file($this->lockFile)) {
			if(!@unlink($this->lockFile))
				throw new Locker_FileLock_Permission_Exception('Permission denied on ' . $this->lockFile);
		}
	}
}

class Locker_FileLock {
	private $tempDir;							// System's temporary directory where we'll store the lock file
	private $lockFile;							// Full path & name of the lock file
	private $sharedLockFile;					// Full path & name of the shared lock file used by this request
	private $lockType = self::LOCK_EXCLUSIVE;	// Type of lock (Exclusive or Shared)
	private $lockExpiry = 172800;				// Seconds before forcing expiration of a lock (default is 48 Hours)
	private $pid;								// PID of the current process
	private $key;								// Key (filename) for this lock
	
	const LOCK_EXCLUSIVE = 1;
	const LOCK_SHARED = 2;
	const LOCK_RELEASE = 4;
	
	/*
	*	Locker_FileLock()
	*
	*	@param string Filename
	*	@param integer Locking mode
	*	@param integer Seconds before timing out on fetching lock
	*	@param integer Time to wait between lock attempts (us)
	*/
	public function __construct($key, $type = Locker_FileLock::LOCK_EXCLUSIVE, $timeout = 10, $pollInterval = 250)
	{
		$this->key = $key;
		$this->pid = getmypid();
		$this->tempDir = sys_get_temp_dir();
		$this->lockFile = $this->tempDir . basename($key) . '.lck';
		$this->sharedLockFile = $this->lockFile . '.' . $this->pid;
		$now = time();
		
		switch($type)
		{
			case self::LOCK_EXCLUSIVE:
				$i=0;
				while(TRUE) {
					if($i / 1000000 > $timeout)
						throw new Locker_FileLock_Timeout_Exception('Failed to obtain lock');

					clearstatcache();
					if(!file_exists($this->lockFile))
					{
						$tmpptr = @fopen($this->lockFile, 'x');
						if(!$tmpptr) continue;
						fwrite($tmpptr, '1'); 
						fclose($tmpptr); 
						
						// Wait for shared locks
						while(TRUE) {
							if($i / 1000000 > $timeout)
								throw new Locker_FileLock_Timeout_Exception('Failed to obtain lock');
							
							$files = glob($this->lockFile . '.*');
							if(empty($files)) break;
							$current = FALSE;
							foreach($files as $file)
							{
								if($now - filemtime($file) < $this->lockExpiry)
								{
									$current = TRUE;
									break;
								} else {
									@unlink($file);
								}
							}
							if(!$current) break;
							
							$i += $sleep = rand(100, $pollInterval);
							usleep($sleep);
							clearstatcache();
						}
						
						break 2;
					}
					
					$i += $sleep = rand(100, $pollInterval);
					usleep($sleep);
				}
				
			case self::LOCK_SHARED:
				$i=0;
				while(TRUE) {
					if($i / 1000000 > $timeout)
						throw new Locker_FileLock_Timeout_Exception('Failed to obtain lock');

					clearstatcache();
					if(!file_exists($this->lockFile))
					{
						$tmpptr = fopen($this->sharedLockFile, 'w');
						if(!$tmpptr) continue;
						fwrite($tmpptr, '2'); 
						fclose($tmpptr);
						break 2;
					}
					
					$i += $sleep = rand(100, $pollInterval);
					usleep($sleep);
				}
			
			case self::LOCK_RELEASE:
				$this->__destruct();
				$this->lockType = 0;
				break;
		}
	}
	
	public function __destruct() {
		if($this->lockType & (self::LOCK_RELEASE | self::LOCK_SHARED) && is_file($this->sharedLockFile))
		{
			if(!@unlink($this->sharedLockFile))
				throw new Locker_FileLock_Permission_Exception('Permission denied on ' . $this->sharedLockFile);
		}
		
		if($this->lockType & (self::LOCK_RELEASE | self::LOCK_EXCLUSIVE) && is_file($this->lockFile)) {
			if(!@unlink($this->lockFile))
				throw new Locker_FileLock_Permission_Exception('Permission denied on ' . $this->lockFile);
		}
	}
}