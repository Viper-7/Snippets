<?php
abstract class Singleton {
	protected static $instances;
	
	public function __construct() {
		$this->instance_name = $instance_name;

		$class = get_called_class();
		
		if(!isset(self::$instances[$class])) {
			self::$instances[$class] = $this;
		}
	}
	
	public static function getInstance() {
		$class = get_called_class();
		
		if(!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class($instance_name);
		}
		
		return self::$instances[$class];
	}
}