<?php
class Tag_ssemail_condition extends CFMLTag {
	public static $required_attributes = array();
	public static $default_attributes = array(
		'type' => 'Checkbox',
		'options' => 'No,Yes'
	);

	public function close() {
		if(isset($this->startTag->attributes['title'])) {
			$label = trim($this->startTag->attributes['title']);
			$name = preg_replace('/\W+/', '_', $label);
		} elseif(isset($this->startTag->attributes['name'])) {
			$name = $this->startTag->attributes['name'];
		}

		if(!$this->getVar($name) && !$this->getVar($name . 'ID'))
			$this->generatedContent = '';
	}

	public function getCMSFields() {
		if(isset($this->attributes['title'])) {
			$label = trim($this->attributes['title']);
			$name = preg_replace('/\W+/', '_', $label);
	
			$class = strtolower($this->attributes['type']);
	
			$source = array_map('trim', explode(',', $this->attributes['options']));
			krsort($source);
	
			switch($class) {
				case 'checkbox':
					$field = new CheckboxField($name, $label);
					break;
				case 'dropdown':
					$field = new DropdownField($name, $label, $source);
				case 'radio':
					$field = new OptionsetField($name, $label, $source);
			}
	
			return new FieldSet($field);
		}
	}
}
