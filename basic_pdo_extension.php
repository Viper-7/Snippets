<?php
class myPDOStatement extends PDOStatement
{
	public $sql;
}

class myPDO extends PDO
{
	public final function __construct($dsn, $user, $pass, $options = array(\PDO::ATTR_EMULATE_PREPARES => FALSE))
	{
			parent::__construct($dsn, $user, $pass, $options);
			$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('myPDOStatement', array($this)));
	}
	
	function prepare($statement, $driver_options = array())
	{
		$stmt = parent::prepare($statement, $driver_options);
		$stmt->sql = $statement;
	}
}