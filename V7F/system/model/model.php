<?php
namespace V7F\Model;
use V7F\Factory as Factory;

class Model {
	public function populate() {
	
	}

	public function dump() {
		print_rr($this);
	}

	public function save() {
		$class = explode('\\', get_called_class());
		$class = array_pop($class);
		
		$factory = 'V7F\\Factory\\' . $class;
		
		$factory = $factory::getInstance();
		
		return $factory->save(array($this));
	}
	
	public function delete() {
		$class = explode('\\', get_called_class());
		$class = array_pop($class);
		
		$factory = 'V7F\\Factory\\' . $class;
		
		$factory = $factory::getInstance();
		
		return $factory->delete(array($this));
	}
	
	public function __call($var, $value = NULL) {
		return $this->$var;
	}
}