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
	
	protected function _init($path, $parent_path = NULL)
	{
		$this->path = $path;
		$this->qualified_path = join_path($parent_path, $path);
	}
	
	public function __get($name)
	{
		if($name == '_props')
			return (object)array('qualified_path' => $this->qualified_path, 'path' => $this->path);
		
		// If there is a child module of that name, return it
		if(isset($this->modules[$name]))
			return $this->modules[$name];

		$qualified_path = join_path($this->qualified_path, $name);

		// If a sub-module folder exists, return a child object
		if(!is_dir($qualified_path))
			throw new LazyLoad_File_Not_Found("Module: \"$qualified_path\" not found");
		
		$class = get_called_class();
		$this->modules[$name] = new $class();
		$this->modules[$name]->_init($name, $this->qualified_path);
		
		return $this->modules[$name];
	}
	
	public function __call($name, $args)
	{
		// If there is a method loaded of that name, execute & return it
		if(isset($this->methods[$name]))
			return call_user_func_array($this->methods[$name], $args);

		$qualified_path = join_path($this->qualified_path, $name);
		$file_path = $qualified_path . '.php';
		
		// Default Case: If the previous checks failed and a method file is not found, throw an error
		if(!file_exists($file_path))
			throw new LazyLoad_File_Not_Found("Method: \"$qualified_path\" not found");
		
		// Include the method
		$qry = include($file_path);
		
		if(!is_callable($qry))
			throw new LazyLoad_File_Not_A_Method("Method: \"$qualified_path\" did not contain a method");
		
		$this->methods[$name] = $qry;
		
		return call_user_func_array($this->methods[$name], $args);
		
	}
	
	public function addChild($path)
	{
		if(isset($this->methods[$path]))
			throw new LazyLoad_Module_Name_Conflict("Module \"$path\" has a name conflict with an existing method");
		
		$qualified_path = join_path($this->qualified_path, $path);
		
		if(!is_dir($path))
			throw new LazyLoad_Module_Not_Found("Module \"$path\" folder not found");
		$qualified_path = join_path($this->qualified_path, $path);
		
		$class = get_called_class();
		$this->modules[$path] = new $class();
		$this->modules[$path]->_init($path, $this->qualified_path);
		
		return $this->modules[$path];
	}
}