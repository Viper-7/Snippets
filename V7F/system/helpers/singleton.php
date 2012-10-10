<?php
namespace V7F\Helpers;

abstract class Singleton {
	protected static $instances = array();
	protected static $master_instance;

	protected $instance_name;
	
	public function __construct($instance_name = '_master') {
		$this->instance_name = $instance_name;

		$class = get_called_class();
		
		if(!isset(self::$instances[$class][$instance_name])) {
			self::$instances[$class][$instance_name] = $this;
			if($instance_name == '_master')
				self::$master_instance = $this;
		}
	}
	
	public static function getInstance($instance_name = '_master') {
		$class = get_called_class();
		
		if(!isset(self::$instances[$class][$instance_name])) {
			self::$instances[$class][$instance_name] = new $class($instance_name);
		}
		
		return self::$instances[$class][$instance_name];
	}
	
	public function setMasterInstance() {
		$oldmaster = TRUE;
		$class = get_called_class();
		
		if(isset(self::$instances[$class]['_master'])) {
			$oldmaster = self::$instances[$class]['_master'];
		}
		
		self::$master_instance = self::$instances[$class]['_master'] = self::$instances[$class][$this->instance_name];
		
		return $oldmaster;
	}
}