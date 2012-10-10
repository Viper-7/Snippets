<?php
abstract class Singleton {
	protected static $instances;
	
	public static function getInstance() {
		$class = get_called_class();
		
		if(!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class;
			
			if(method_exists(self::$instances[$class], '_init'))
			{
				$args = func_get_args();
				call_user_func_array(array(self::$instances[$class], '_init'), $args);
			}
		}

		return self::$instances[$class];
	}
}