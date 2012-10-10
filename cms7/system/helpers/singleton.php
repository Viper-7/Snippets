<?php
abstract class Singleton {
	protected static $instances;
	
	public static function getInstance() {
		$class = get_called_class();
		
		if(!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class;
		}

		$instance = self::$instances[$class];

		if(method_exists($instance, '_init'))
		{
			$args = func_get_args();
			call_user_func_array(array($instance, '_init'), $args);
		}
		
		return $instance;
	}
}