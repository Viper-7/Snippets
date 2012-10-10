<?php
class Template {
	public function render($template, $vars = NULL) {
		if(!isset($vars)) $vars = array();
		if(is_object($vars)) $vars = get_object_vars($vars);
		
		if(file_exists($template)) {
			$vars = array_merge(get_object_vars($this), $vars);

			ob_start();
			
			extract($vars);
			include($template);
			
			$content = ob_get_clean();
			
			$assetUrl = join_path(Config::get('webroot'), 'ajax/getSecureAsset');
			$content = preg_replace('/<!\{asset:(\d+)\}>/', '<img src="' . $assetUrl . '/asset/\1"/>', $content);
			
			return $content;
		} else {
			trigger_error('Template did not exist: ' . $template, E_USER_ERROR);
		}
	}
}