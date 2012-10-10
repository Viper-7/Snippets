<?php
class Registry extends Singleton
{
	private static $props;

	public static function get($var)
	{
		$class = get_called_class();

		if(isset(self::$props[$class][$var]))
			return self::$props[$class][$var];

		return NULL;
	}

	public static function set($var, $value)
	{
		$class = get_called_class();

		self::$props[$class][$var] = $value;
	}

	public static function getAllProps()
	{
		$class = get_called_class();

		return self::$props[$class];
	}

	public function __get($var)
	{
		return self::get($var);
	}

	public function __set($var, $value)
	{
		return self::set($var, $value);
	}
}