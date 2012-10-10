<?php
namespace V7F\Helpers;

final class Registry extends Singleton {
	private $data;
	
	public function __get($var) {
		if(isset($this->data[$var])) {
			return $this->data[$var];
		} else {
			return NULL;
		}
	}
	
	public function __set($var, $value) {
		$this->data[$var] = $value;
	}
	
	public function __call($var, $value) {
		if(empty($value)) {
			return $this->data[$var];
		} else {
			return $this->data[$var] = $value;
		}
	}
	
	
	public static function __callStatic($var, $value) {
		$instance = self::getInstance();
		if(empty($value)) {
			return $instance->data[$var];
		} else {
			return $instance->data[$var] = $value;
		}
	}
	
	public static function set($var, $value) {
		$instance = self::getInstance();
		return $instance->$var = $value;
	}
	
	public static function get($var) {
		$instance = self::getInstance();
		return $instance->$var;
	}
}
