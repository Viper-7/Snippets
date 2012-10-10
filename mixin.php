<?php
/**
* Mixin Class
*
* @Author Dale Horton
* @Date 2009-12-19
* @Example http://home.viper-7.com/trac/browser/PHP/mixin_example.php
*
* This class is designed to allow mixing of multiple classes and/or objects 
* into a single interface, otherwise known as multiple inheritance or mixins.
* This has only recently become possible in a clean way with 5.3 adding 
* __callStatic, get_called_class() and some other functionality.
*
* Note: All child classes must implement the Mixable interface.
* Note: Static properties are NOT supported! You must use getters/setters to 
* 		provide access to static properties.
*
* ----------------------------------------------------------------------------
* "THE BEER-WARE LICENSE" (Revision 42):
* <viper7@viper-7.com> wrote this file. As long as you retain this notice you
* can do whatever you want with this stuff. If we meet some day, and you think
* this stuff is worth it, you can buy me a beer in return.   Dale Horton
* ----------------------------------------------------------------------------
**/

interface Mixable {}
abstract class Mixin
{
	protected static $classes = Array();
	protected static $mixins = Array();
	protected $instances = Array();
	
	public function __construct()
	{
		$mixinname = get_called_class();
		
		Mixin::$mixins[$mixinname][] = $this;
	}
	
	/**
	* Adds a class to a Mixin
	*
	* @param string $class_name Name of the class to import
	**/
	public static function inheritStatic($class_name)
	{
		if(!class_exists($class_name)) trigger_error("Class $class_name does not exist", E_USER_ERROR);

		if(!in_array('Mixable', class_implements($class_name))) trigger_error("Class $class_name is not Mixable");

		$mixinname = get_called_class();
		
		array_unshift(Mixin::$classes[$mixinname], $class_name);
	}

	/**
	* Adds an object instance to a Mixin
	*
	* @param string $class_name Name of the class to create an instance from
	*
	* Further arguments can be supplied which will be passed to the object's constructor
	**/
	public function inherit($class_name)
	{
		if(!in_array('Mixable', class_implements($class_name))) trigger_error("Class $class_name is not Mixable");
		
		$args = func_get_args();
		array_shift($args); // remove $class_name from constructor argument array
		
		$codeargs = Array();

		$code = "return new $class_name(";
		
		foreach($args as $key => $value)
		{
			$codeargs[] = "\$args[$key]";
		}

		$code .= implode(', ', $codeargs);
		$code .= ");";

		$this->instances = array_merge(array($class_name => eval($code)), $this->instances);
	}
	
	/**
	* Looks up a base Mixin instance from one of its child objects
	*
	* @param $obj Child object to search for
	**/
	public static function getMixin($obj)
	{
		foreach(Mixin::$mixins as $mixins)
		{
			foreach($mixins as $mixin)
			{
				foreach($mixin->instances as $instance)
				{
					if($obj === $instance)
					{
						return $mixin;
					}
				}
			}
		}
	}
	
	/**
	* Provides access to read object properties
	**/
	public function __get($name)
	{
		foreach($this->instances as $class => $obj)
		{
			if(property_exists($obj, $name))
			{
				return $obj->$name;
			}
		}
	}
	
	/**
	* Provides access to write to object properties
	**/
	public function __set($name, $value)
	{
		foreach($this->instances as $class => $obj)
		{
			if(property_exists($obj, $name))
			{
				return $obj->$name = $value;
			}
		}
		
		$this->$name = $value;
	}
	
	/**
	* Provides access to check the existance of object properties
	**/
	public function __isset($name)
	{
		foreach($this->instances as $class => $obj)
		{
			if(property_exists($obj, $name))
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	* Provides access to delete object properties
	**/
	public function __unset($name)
	{
		foreach($this->instances as $class => $obj)
		{
			if(property_exists($obj, $name))
			{
				unset($obj->$name);
			}
		}
	}

	/**
	* Provides access to object methods
	**/
	public function __call($name, $arguments)
	{
		foreach($this->instances as $class => $obj)
		{
			if(method_exists($obj, $name))
			{
				return call_user_func_array(array($obj, $name), $arguments);
			}
		}
		
		trigger_error("Method $name not found", E_USER_ERROR);
	}
	
	/**
	* Provides access to object methods provided by classes that were
	* inherit()'ed into the current Mixin before the specified child.
	* 
	* @param $childobj Child class to fetch parents for (usually $this)
	* @param $name Method name to call
	* Additional arguments are passed to the function called
	*
	* <code>
	* Class Animal implements Mixable
	* {
	*   public function walk($legs)
	*   {
	*     echo "I walk on $legs legs";
	*   }
	* }
	*
	* Class Dog implements Mixable
	* {
	*   public function walk()
	*   {
	*     $mixin = Mixin::getMixin($this);
	*     $mixin->parentCall($this, 'walk', 4);
	*   }
	* }
	* </code>
	**/
	public function parentCall($childobj, $name)
	{
		$arguments = func_get_args();
		array_shift($arguments); array_shift($arguments);
		
		$child_found = FALSE;
		
		foreach($this->instances as $class => $obj)
		{
			if($child_found && method_exists($obj, $name))
			{
				return call_user_func_array(array($obj, $name), $arguments);
			}

			if($obj === $childobj) $child_found = TRUE;
		}
		
		trigger_error("Method $name not found in parents", E_USER_ERROR);
	}
	
	/**
	* Provides access to static methods
	*
	* @param $name Name of the static method
	* @param $arguments Array of arguments to pass to the static method
	**/
	public static function __callStatic($name, $arguments)
	{
		$mixinname = get_called_class();

		foreach(Mixin::$classes[$mixinname] as $class)
		{
			if(method_exists($class, $name))
			{
				$return = call_user_func_array(array($class, $name), $arguments);
			}
		}
		
		if(isset($return)) return $return;
		
		foreach(Mixin::$mixins[$mixinname] as $mixin)
		{
			foreach($mixin->instances as $class => $obj)
			{
				if(method_exists($class, $name))
				{
					return call_user_func_array(array($class, $name), $arguments);
				}
			}
		}
	}
}