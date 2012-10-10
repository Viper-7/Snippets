<?php
namespace V7F\Helpers;
use \PDO, \PDOStatement;

class DB extends Singleton {
	public $dbh;
	
	public function __construct($instance) {
		parent::__construct($instance);
		$this->dbh = new DB_PDO($instance);
	}
	
	public function __call($func, $vars = array()) {
		return call_user_func_array(array($this->dbh, $func), $vars);
	}
}

class DB_PDO extends PDO {
	public final function __construct($instance = NULL) {
		$config = Registry::getInstance($instance);
		
		$dsn = $config->db_engine . ':dbname=' . $config->db_dbname . ';host=' . $config->db_host;
		$options = array(PDO::ATTR_EMULATE_PREPARES => FALSE);

		try {
			parent::__construct($dsn, $config->db_user, $config->db_pass, $options);
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('V7F\Helpers\DB_PDO_Statement', array($this)));
		} catch (PDOException $e) {
			trigger_error('DB: Connection failed: ' . $e->getMessage(), E_USER_ERROR);
		}
	}
	
	public function prepared_query($sql, $vars, $options) {
		$stmt = $this->prepare($sql);

		$stmt->execute($vars);
		return $stmt->fetchAll($options);
	}
	
	public function prepare($sql, $options = array()) {
		$stmt = parent::prepare($sql, $options);
		if($stmt === FALSE) trigger_error('DB Error - ' . $this->db->errorInfo(), E_USER_ERROR);
		return $stmt;
	}
}

class DB_PDO_Statement extends PDOStatement {
	public $dbh;

	protected function __construct($dbh) {
		$this->dbh = $dbh;
	}

	public function fetchObjs($classname, $options = 0) {
		$data = $this->fetchAll($options | PDO::FETCH_ASSOC);
		if($data === FALSE) trigger_error('DB Notice - No records to fetch', E_USER_NOTICE);
		$out = array();
		
		foreach($data as $element) {
			$obj = new $classname();
			foreach($element as $field => $value) {
				$obj->$field = $value;
			}
			
			if(method_exists($obj, 'populate')) { 
				$obj->populate();
			}
			
			$out[] = $obj;
		}
		
		return $out;
	}
}