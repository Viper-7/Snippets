<?php
namespace V7F\Editor;
use V7F\Helpers\Singleton, V7F\Helpers\Registry;

require_once ltrim(join_path(Registry::get('base_dir'), 'system/editor/fckeditor', 'fckeditor.php'),DIRECTORY_SEPARATOR);

class Editor extends Singleton {
	public function TextBox($name, $value = "") {
		$sBasePath = join_path(Registry::get('base_dir'), 'system/editor/fckeditor/');
		
		$oFCKeditor = new \FCKeditor($name);
		$oFCKeditor->BasePath = $sBasePath;
		$oFCKeditor->Value = $value;
		$oFCKeditor->Width = 800;
		$oFCKeditor->Height = 700;
		
		ob_start();
		
		$oFCKeditor->Create();
		unset($oFCKeditor);
		
		return ob_get_clean();
	}
}
