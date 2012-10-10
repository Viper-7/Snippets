<?php
class LazyLoad_Exception extends Exception {}
class LazyLoad_File_Not_Found extends LazyLoad_Exception {}
class LazyLoad_File_Not_A_Method extends LazyLoad_Exception {}
class LazyLoad_Module_Not_Found extends LazyLoad_Exception {}
class LazyLoad_Module_Name_Conflict extends LazyLoad_Exception {}

class LazyLoad extends Singleton
{
	protected $path;
	protected $qualified_path;
	protected $methods;
	protected $modules;
	
	protected static $file_manifest;
	protected static $folder_manifest;
	protected static $base_path;
	
	protected function _init($path = NULL, $parent_path = NULL)
	{
		if($parent_path === NULL) {
			$parent_path = getcwd();
		}
		
		if($path === NULL) {
			$class = get_called_class();
			$path = $class::$base_path;
		}

		$this->path = $path;
		$this->qualified_path = self::join_path($parent_path, $path);
	}
	
	public function __get($name) {
		if($name == '_props')
			return (object)array('qualified_path' => $this->qualified_path, 'path' => $this->path);
		
		return $this->getModule($name);
	}
	
	public function __call($name, $args) {
		$method = $this->getMethod($name);
		
		return call_user_func_array($method, $args);
	}
	
	public static function __callStatic($name, $args) {
		$inst = static::getInstance();
		if($args)
			return call_user_func_array(array($inst, $name), $args);
		else
			return $inst->$name;
	}

	public function getMethod($name) {
		$qualified_path = self::join_path($this->qualified_path, $name);
		$file_path = $qualified_path . '.php';

		if(!file_exists($file_path))
			throw new LazyLoad_File_Not_Found("Method: \"$qualified_path\" not found");

		if(!isset($this->methods[$name])) {
			// Include the method
			$qry = include($file_path);
			
			if(!is_callable($qry))
				throw new LazyLoad_File_Not_A_Method("Method: \"$qualified_path\" did not contain a method");
			
			$this->methods[$name] = $qry;
		}

		return $this->methods[$name];
	}
	
	public function getModule($name) {
		if(!isset($this->modules[$name])) {
			$qualified_path = self::join_path($this->qualified_path, $name);
			$file_path = $qualified_path . '.php';

			// If a method exists with that name, return it
			if(file_exists($file_path))
				return $this->getMethod($name);

			// If no sub-module folder exists, we're in trouble
			if(!file_exists($qualified_path) || !is_dir($qualified_path))
				throw new LazyLoad_File_Not_Found("Module: \"$qualified_path\" not found");
			
			// If a sub-module folder exists, return a child object
			$class = get_called_class();
			$this->modules[$name] = new $class();
			$this->modules[$name]->_init($name, $this->qualified_path);
		}
		
		return $this->modules[$name];
	}
	
	public static function join_path() {
		$args = func_get_args();
		array_map(function($path) { return rtrim($path, DIRECTORY_SEPARATOR); }, $args);
		return implode(DIRECTORY_SEPARATOR, $args);
	}
}
