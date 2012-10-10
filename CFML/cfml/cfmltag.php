<?php
class CFMLTag {
	public $name;
	public $token;
	public $tagScope;
	public $attributes;
	public $parentTag;
	public $startIndex;
	public $generatedContent;
	
	public static $required_attributes = array();
	public static $default_attributes = array();
	
	public function getName() {
		return $this->name;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setVar($var, $val, $scope = 'global') {
		if($scope == 'local' || isset($this->tagScope[$var]))
			return $this->tagScope[$var] = $val;
		
		CFML::$variables[$var] = $val;
	}
	
	public function getVar($var) {
		if(isset($this->tagScope[$var]))
			return $this->tagScope[$var];
		
		if(isset(CFML::$variables[$var]))
			return CFML::$variables[$var];
		
		return NULL;
	}
	
	public static function createFromToken($token) {
		if(!class_exists($tagclass = 'Tag_' . $token['tag'])) {
			$tagclass = 'CFMLTag';
		}
		
		$tag = new $tagclass();
		
		$tag->token = $token;
		$tag->type = $token['type'];
		$tag->name = $token['tag'];
		$tag->attributes = $token['attributes'];
		
		if($token['mode'] != 'end') {
			foreach($tagclass::$required_attributes as $attribute) {
				if(!isset($tag->attributes[$attribute]))
					throw new CFML_Syntax_Exception($token['tag'] . ': ' . $attribute . ' attribute is required.');
			}
			
			foreach($tagclass::$default_attributes as $attribute => $value) {
				if(!isset($tag->attributes[$attribute]))
					$tag->attributes[$attribute] = $value;
			}
		}
		
		return $tag;
	}
	
	public function getCMSFields() {
		return new FieldSet();
	}
	
	public function open() {
	}
	
	public function close() {
	}
	
	public function execute() {
		$this->open();
		return $this->close();
	}
}
