<?php
class Tag_ssloop extends CFMLTag {
	public static $required_attributes = array('from', 'to', 'index');
	public static $default_attributes = array('step' => 1);
	
	public function open() {
		if(!isset($this->attributes['step']))
			$this->attributes['step'] = 1;

		$this->setVar($this->attributes['index'], $this->attributes['from']);
	}
	
	public function close() {
		$attr = $this->startTag->attributes;
		$index = $this->getVar($attr['index']);

		if($attr['from'] > $attr['to']) {
			if($index <= $attr['to'])
				return;
		} else {
			if($index >= $attr['to'])
				return;
		}

		$index += $attr['step'];
		$this->setVar($attr['index'], $index);
		
		CFMLParser::gotoTag($this->startTag);
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->push(new TextField($this->attributes['index']));
		return $fields;
	}
}