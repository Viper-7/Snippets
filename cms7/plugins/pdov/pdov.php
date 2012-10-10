<?php
class PDOV_Exception extends \PDOException { }

class PDOV extends \PDO
{
	protected $sql;
	protected $data;
	protected $params;
	protected $paramTypes;
	protected $paramNames;
	
	public $cache = '';
	public $cacheObj;
	public $cachePeriod = 30;

	public $profile = FALSE;
	public $startProfile;
	public $use_globals = FALSE;
	public $query_log = array();
	public $log_queries = FALSE;
	
	protected static $instances = array();
	protected static $master_instance;

	protected $instance_name;
	
	public static function getInstance($instance_name = '_master') {
		$class = get_called_class();
		
		return self::$instances[$class][$instance_name];
	}
	
	public static function createInstance($instance_name = '_master', $server, $user, $pass, $db, $engine = 'mysql', $options = array(\PDO::ATTR_EMULATE_PREPARES => FALSE))
	{
		$class = get_called_class();
		
		$obj = new $class($server, $user, $pass, $db, $engine, $options);
		$obj->instance_name = $instance_name;
		
		if(!isset(self::$instances[$class][$instance_name])) {
			self::$instances[$class][$instance_name] = $obj;
			if($instance_name == '_master')
				self::$master_instance = $obj;
		}
		
		return $obj;
	}
	
	public function __destruct()
	{
		if($this->log_queries)
		{
			echo '<br/><br/><div style="width:100%; background-color: #CCC; margin: 0 auto; color: #000; text-align: center; padding: 2px;"><strong>Query Profiles</strong></div><br/>' . implode('<br/>', $this->query_log);
		}
	}
	
	public final function __construct($server, $user, $pass, $db, $engine = 'mysql', $options = array(\PDO::ATTR_EMULATE_PREPARES => FALSE))
	{
		if($db)
		{
			$dsn = $engine . ':dbname=' . $db . ';host=' . $server;
		} else {
			$dsn = $engine . ':' . $server;
		}
		
		try {
			parent::__construct($dsn, $user, $pass, $options);
			$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array(__NAMESPACE__ . '\PDOV_Statement', array($this)));
		} catch (\PDOException $e) {
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
			$param = $this->data[count($this->params)];
		}
		else
		{
			$var = $match[2];
			
			if(is_object($this->data) && property_exists($this->data, $var))
			{
				$param =& $this->data->$var;
			}
			elseif(is_array($this->data) && array_key_exists($var, $this->data))
			{
				$param =& $this->data[$var];
			}
			elseif(is_object($this->data) && method_exists($this->data, $var))
			{
				$param = $this->data->$var();
			}
			elseif(is_scalar($this->data))
			{
				$param =& $this->data;
			}
			elseif(is_array($this->data) && array_key_exists(count($this->params), $this->data))
			{
				$param =& $this->data[count($this->params)];
			}
			elseif($this->use_globals && array_key_exists($var, $GLOBALS))
			{
				$param =& $GLOBALS[$var];
			}
			else
			{
				$param = NULL;
			}
		}
		
		if($param === NULL) 
			return '{{{/=/IS/}}}';
		
		$this->paramNames[] = $match[2];
		$this->params[] = $param;
		end($this->params);
		$this->paramTypes[key($this->params)] = strlen($match[1]) ? $match[1] : 's';
		
		return '?';
	}
	
	public function prepared_query($sql, $data = NULL, $options = array())
	{
		$this->startProfile = microtime(true);
		
		$this->paramNames = array();
		$this->paramTypes = array();
		
		$paramCodes = array(
				'b' => \PDO::PARAM_BOOL,
				'n' => \PDO::PARAM_NULL,
				'i' => \PDO::PARAM_INT,
				's' => \PDO::PARAM_STR,
				'l' => \PDO::PARAM_LOB
				);

		$paramNames = array(
				'b' => '(Boolean)',
				'n' => '(Null)   ',
				'i' => '(Integer)',
				's' => '(String) ',
				'l' => '(LOB)    '
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
		$sql = preg_replace('#=\s*\{\{\{\/=\/IS\/\}\}\}#', 'IS NULL', $sql);
		
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
		
		if($this->log_queries)
		{
			$debug_params = '';
			foreach($this->params as $key => $value)
			{
				$value = htmlentities($value, ENT_QUOTES);
				$debug_params .= "<tr><th>{$key}:</th><td>{$paramNames[$this->paramTypes[$key]]}</td><td>{$this->paramNames[$key]}</td><td>=</td><td>{$value}</td></tr>";
			}
			$debug_return = $stmt->rowCount() . ' rows returned, took ' . number_format((microtime(true) - $this->startProfile) * 100, 2) . 'ms.';
			
			$backtrace = debug_backtrace(TRUE);
			$context = next($backtrace);
			if($context['function'] == '{closure}')
			{
				next($backtrace);
				$context = next($backtrace);

				if($context['class'] = 'LazyLoad')
				{
					$path = $context['object']->_props->path;
					
					$context = next($backtrace);
					$queryfunc = $context['function'];
					$context = next($backtrace);
					
					$contextText = "LazyLoad query {$path}/{$queryfunc}() called from {$context['function']}() in {$context['file']}:{$context['line']}";
				} else {
					prev($backtrace);
				}
			}
			
			if(!isset($contextText))
				$contextText = "Inline query from {$context['function']}() in {$context['file']}:{$context['line']}";
			
			$logentry = <<<EOI
<br/><br/>
<fieldset class="debug pdov querylog entry">
	<legend>{$contextText}</legend>
	<fieldset class="debug pdov querylog sql">
		<legend>SQL</legend>
		<pre>{$sql}</pre>
	</fieldset>
	<fieldset class="debug pdov querylog params">
		<legend>Params</legend>
		<table>{$debug_params}</table>
	</fieldset>
	<pre>{$debug_return}</pre>
</fieldset>
EOI;
			$this->query_log[] = $logentry;
		}

		return $stmt;
	}
}

class PDOV_Statement extends \PDOStatement 
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

	public function fetch($options = NULL, $cursor_orientation = NULL, $cursor_offset = NULL)
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
	
	public function fetchColumn($options = NULL)
	{
		throw new PDOV_Exception('fetchColumn() not implemented in PDOV');
	}
	
	public function fetchAll($options = NULL, $column_index = NULL, $ctor_args = NULL)
	{
		if($this->dbh->cache && $this->cachedData)
		{
			$data = $this->cachedData;
		} else {
			$data = parent::fetchAll($options | \PDO::FETCH_ASSOC);

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