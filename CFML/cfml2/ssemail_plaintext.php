<?php
class Tag_ssemail_plaintext extends CFMLTag {
	public static $required_attributes = array('name');

	public function close() {
		echo $this->getVar($this->attributes['name']);
	}

	public function getCMSFields() {
		$name = $this->attributes['name'];
		
		return new FieldSet(new TextareaField($name));
	}
}
