<?php
class Tag_ssgrid_column extends CFMLTag {
	public static $required_attributes = array('name');

	public function close() {
		if($this->getVar('preview')) {
			echo $this->getVar('previewSlot');
		} else {
			$slot = $this->getVar('Me')->Slot($this->attributes['name']);
			if(is_object($slot))
				echo $slot->forTemplate();
			else
				echo $slot;
		}
	}

	public function createSlot($gridPage) {
		return $gridPage->createSlot($this->attributes['name']);
	}
}

