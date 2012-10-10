<?php
class CFMLTag extends Object {
	public $name;
	public $token;
	public $tagScope;
	public $attributes;
	public $startTag;
	public $startIndex;
	public $generatedContent;

	public static $required_attributes = array();
	public static $default_attributes = array();
	public static $current_tag = null;
	

	public static function getCurrentTag() {
		return self::$current_tag;
	}
	
	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->type;
	}

	public function getContainer($name = NULL, $depth = 1) {
		return CFMLParser::getContainer($name, $depth);
	}

	public function setVar($var, $val, $scope = 'global') {
		if($scope == 'local' || isset($this->tagScope[$var]))
			return $this->tagScope[$var] = $val;

		CFML::$variables[$var] = $val;
	}

	public function getVar($var) {
		if(isset($this->tagScope[$var]))
			return $this->tagScope[$var];

		if(isset($this->attributes[$var]))
			return $this->attributes[$var];

		if(isset(CFML::$variables[$var]))
			return CFML::$variables[$var];

		if(CFML::$fallback->hasMethod($var))
			return CFML::$fallback->$var();

		if(CFML::$fallback->hasField($var))
			return CFML::$fallback->getField($var);

		return NULL;
	}

	public static function createFromToken($token) {
		if(!isset($token['tag']) || !class_exists($tagclass = 'Tag_' . $token['tag'])) {
			$tagclass = 'CFMLTag';
		}

		$tag = new $tagclass();

		$tag->token = $token;
		$tag->type = $token['type'];

		if(isset($token['tag']))
			$tag->name = $token['tag'];

		if(isset($token['attributes']))
			$tag->attributes = $token['attributes'];

		if(isset($token['mode']) && $token['mode'] != 'end') {

			foreach ($tagclass::$required_attributes as $attribute) {

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
		self::$current_tag = null;
	}

	public function execute() {
		$this->open();
		return $this->close();
	}
}
