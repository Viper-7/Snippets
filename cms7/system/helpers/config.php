<?php
class Config extends Registry { 
	public static function url($path)
	{
		return join_path(self::get('webroot'), $path);
	}
}