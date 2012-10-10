<?php
class PDOV_Exception extends PDOException { }

class PDOV extends PDO
{
	protected $sql;
	protected $data;
	protected $params;
	protected $paramTypes;
	
	public $cache = '';
	public $cacheObj;
	public $cachePeriod = 30;

	public $profile = TRUE;
	public $startProfile;
	public $use_globals = FALSE;
	
	public final function __construct($server, $user, $pass, $db, $engine = 'mysql', $options = array(PDO::ATTR_EMULATE_PREPARES => FALSE))
	{
		if($db == '' && $user == '' && $pass == '')
			$dsn = $engine . ':' . $server;
		else
			$dsn = $engine . ':dbname=' . $db . ';host=' . $server;
			
		try {
			parent::__construct($dsn, $user, $pass, $options);
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOV_Statement', array($this)));
		} catch (PDOException $e) {
			throw new PDOV_Exception('DB: Connection failed: ' . $e->getMessage());
		}
	}
	
	public function useMemcache($memcache)
	{
		$this->cacheObj = $memcache;
		$this->cache = 'memcached';
	}
	
	public function useAPC()
	{
		$this->cache = 'apc';
	}
	
	public function disableCache()
	{
		$this->cache = '';
	}
	
	public function getCached($key)
	{
		switch($this->cache)
		{
			case 'apc':
				return apc_fetch($key);
				break;
			case 'memcached':
				return $this->cacheObj->get($key);
				break;
		}
	}
	
	public function setCache($key, $val)
	{
		switch($this->cache)
		{
			case 'apc':
				return apc_store($key, $val);
				break;
			case 'memcached':
				return $this->cacheObj->set($key, $val, NULL, $this->cachePeriod);
				break;
		}
	}
	
	protected function bindVar($match)
	{
		if($match == '?')
		{
			$this->params[] = $this->data[count($this->params)];
		}
		else
		{
			$var = $match[2];
			
			if(is_object($this->data) && property_exists($this->data, $var))
			{
				$this->params[] =& $this->data->$var;
			}
			elseif(is_array($this->data) && array_key_exists($var, $this->data))
			{
				$this->params[] =& $this->data[$var];
			}
			elseif(is_object($this->data) && method_exists($this->data, $var))
			{
				$this->params[] = $this->data->$var();
			}
			elseif(is_scalar($this->data))
			{
				$this->params[] =& $this->data;
			}
			elseif(is_array($this->data) && array_key_exists(count($this->params), $this->data))
			{
				$this->params[] =& $this->data[count($this->params)];
			}
			elseif($this->use_globals && array_key_exists($var, $GLOBALS))
			{
				$this->params[] =& $GLOBALS[$var];
			}
			else
			{
				$this->params[] = NULL;
			}
		}
		
		end($this->params);
		$this->paramTypes[key($this->params)] = strlen($match[1]) ? $match[1] : 's';
		
		return '?';
	}
	
	public function preparedQuery($sql, $data = NULL, $options = array())
	{
		if($this->profile) $this->startProfile = microtime(true);
		
		$paramCodes = array(
				'b' => PDO::PARAM_BOOL,
				'n' => PDO::PARAM_NULL,
				'i' => PDO::PARAM_INT,
				's' => PDO::PARAM_STR,
				'l' => PDO::PARAM_LOB
				);

		if(is_scalar($data) || $data == NULL)
		{
			$args = func_get_args();
			if(is_scalar($options))
			{
				$options = array();
				$data = array_slice($args,1);
			}
			elseif(count($args) > 3) {
				$options = array();
				$data = array_slice($args,3);
			}
		}
		
		$this->data = $data;
		$this->sql = $sql;
		$this->params = Array();
		
		$sql = preg_replace_callback('/(?:(\w?)\:([^\:]+)\:)|(\?)/', Array($this, 'bindvar'), trim($sql));

		if($this->cache)
		{
			$key = md5($this->sql . serialize($this->params));
			if($cached = $this->getCached($key))
			{
				$stmt = PDOV_Statement::getCached($this, unserialize($cached));
				return $stmt;
			}
		}

		$stmt = $this->prepare($sql, $options);
		if($stmt === FALSE)
		{
			$error = $this->errorInfo();
			throw new PDOV_Exception('DB Error - ' . $error[2]);
		}

		$stmt->sql = $this->sql;
		$stmt->params = $this->params;

		foreach($this->params as $key => $value)
		{
			$stmt->bindValue($key+1, $value, $paramCodes[$this->paramTypes[$key]]);
		}
		
		$stmt->execute();

		return $stmt;
	}
}

class PDOV_Statement extends PDOStatement 
{
	public $dbh;
	public $cachedData;
	public $params;
	public $sql;
	public $cacheHit = FALSE;
	
	protected function __construct($dbh)
	{
		$this->dbh = $dbh;
	}
	
	public static function getCached($dbh, $data)
	{
		$stmt = new PDOV_Statement($dbh);
		$stmt->cachedData = $data;
		$stmt->cacheHit = TRUE;
		return $stmt;
	}

	public function fetch($options = NULL)
	{
		if($this->dbh->cache && $this->cachedData)
		{
			$out = current($this->cachedData);
			next($this->cachedData);
			return $out;
		} else {
			return parent::fetch($options);
		}
	}
	
	public function fetchObject($class = 'StdClass', $options = NULL)
	{
		if($this->dbh->cache && $this->cachedData)
		{
			$data = current($this->cachedData);
			next($this->cachedData);
			
			$obj = new $class();
			foreach($data as $key => $val)
			{
				$obj->$key = $val;
			}
			
			if(method_exists($obj, '__wakeup'))
			{ 
				$obj->__wakeup();
			}
			
			return $obj;
		} else {
			return parent::fetchObject($class, $options);
		}
	}
	
	public function fetchColumn()
	{
		throw new PDOV_Exception('fetchColumn() not implemented in PDOV');
	}
	
	public function fetchAll($options = NULL)
	{
		if($this->dbh->cache && $this->cachedData)
		{
			$data = $this->cachedData;
		} else {
			$data = parent::fetchAll($options | PDO::FETCH_ASSOC);

			if($this->dbh->cache)
			{
				$key = md5($this->sql . serialize($this->params));
				$this->dbh->setCache($key, serialize($data));
			}
		}
		
		if($this->dbh->profile) 
		{
			$endProfile = microtime(true) - $this->dbh->startProfile;
			echo 'Query took ' . number_format($endProfile,4) . ' seconds' . ($this->cacheHit ? ' (CACHED)' : '') . '<br/>';
		}

		return $data;
	}
	
	public function fetchObjs($classname = 'StdClass', $options = 0)
	{
		$data = $this->fetchAll($options);

		if($data === FALSE) return NULL;
		
		$out = array();
		
		foreach($data as $element)
		{
			$obj = new $classname();
			foreach($element as $field => $value)
			{
				$obj->$field = $value;
			}
			
			if(method_exists($obj, '__wakeup'))
			{ 
				$obj->__wakeup();
			}
			
			$out[] = $obj;
		}
		
		if($this->dbh->profile) 
		{
			$endProfile = microtime(true) - $this->dbh->startProfile;
			echo 'Query took ' . number_format($endProfile,4) . ' seconds' . ($this->cacheHit ? ' (CACHED)' : '') . '<br/>';
		}

		return $out;
	}
}