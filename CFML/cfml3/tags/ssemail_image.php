<?php
class Tag_ssemail_image extends CFMLTag {
	public static $required_attributes = array('name');

	public function close() {
		$id = $this->getVar($this->attributes['name'] . 'ID');
		if(!$id) return;

		$image = DataObject::get_by_id('Image', $id);

		if($image) {
			/* image specific attribute handling */
			if(!empty($this->attributes['width'])) {
				$newimage = $image->setWidth($this->attributes['width']);
				if($newimage) $image = $newimage;
			}

			if(!empty($this->attributes['height'])) {
				$newimage = $image->setHeight($this->attributes['height']);
				if($newimage) $image = $newimage;
			}

			$path = $image->Filename;
			$url = $image->getURL();

			if(strpos($url, 'http://') !== 0) { 
				$url = '/' . ltrim($url, '/');
			}
			
/*				if(substr($path,0,1) !== '/') 
					$path = HOME_PATH . '/../userhomes/' . NSite::current_site()->PathName . '/' . $path;

				$url = '/' . ltrim(Director::makeRelative(realpath($path)), '/');
			}
*/		
			if(file_exists($path)) {
				if(parse_url($url, PHP_URL_HOST) == '') {	// Convert relative url to absolute
					$url = Director::protocolAndHost() . '/' . $url;
				}

				$title = ($image->Title) ? $image->Title : $image->Filename;
				if($title) {
					$title = Convert::raw2att($title);
				} else {
					if(preg_match("/([^\/]*)\.[a-zA-Z0-9]{1,6}$/", $title, $matches)) $title = Convert::raw2att($matches[1]);
				}
				echo "<img src=\"$url\" alt=\"$title\"";
				
				$copy_attributes = array('style','align');
				foreach($copy_attributes as $name) {
					if(isset($this->attributes[$name])) {
						echo ' ' . $name . '="' . Convert::raw2att($this->attributes[$name]) . '"';
					}
				}

				echo " />";
			}
		}
	}

	public function getCMSFields() {
		$name = $this->attributes['name'];

		return new FieldSet(new ImageField($name));
	}
}
