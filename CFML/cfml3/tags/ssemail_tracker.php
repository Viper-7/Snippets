<?php
class Tag_ssemail_tracker extends CFMLTag {
	public function close() {
		$url = $this->getVar('TrackingImageURL');

		echo '<iframe src="' . $url . '" frameborder="0" border="0" marginwidth="0" marginheight="0" width="1" height="1" style="height: 1px !important; width: 1px !important; border-width: 0pt !important; margin: 0pt !important; padding: 0pt !important; display: block;"></iframe>';
		echo '<img moz-do-not-send="true" src="' . $url . '" style="height: 1px !important; width: 1px !important; border-width: 0pt !important; margin: 0pt !important; padding: 0pt !important; display: block;" width="1" border="0" height="1"/>';
	}
}
