<?php
namespace V7F\Template;
use V7F\Helpers\Singleton, V7F\Helpers\Registry, V7F\Editor\Editor;


class Template extends Singleton {
	public function render($template, $vars) {
		$registry = Registry::getInstance();

		require_once join_path($registry->web_root, 'system/template/h2o.php');
		
		$path = join_path($registry->web_root, 'view/' . $template . '.html');
		
		if(!file_exists($path)) {
			trigger_error('Template: View ' . $path . ' not found!');
		}
		
		$vars = array_merge($vars, array('config' => $registry));

		$h2o = new h2o($path, array('cache' => 'apc', 'safeClass' => array('V7F\\Helpers\\Registry', 'V7F\\Template\\Template_Helpers')));
		echo $h2o->render($vars);
	}
}