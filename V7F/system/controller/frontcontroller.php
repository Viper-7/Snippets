<?php
namespace V7F\Controller;
use V7F\Helpers\Registry, V7F\Helpers\Singleton;

class FrontController extends Singleton {
	public function handleRequest() {
		$config = Registry::getInstance();
		
		$scriptdir = str_replace($config->web_root, '', $config->site_root);
		$scriptname = basename($_SERVER['SCRIPT_FILENAME']);
		
		$request = trim(str_replace(join_path($scriptdir, $scriptname), '', $_SERVER['REQUEST_URI']), DIRECTORY_SEPARATOR);

		$config->base_dir = $scriptdir;
		
		$view = 'default';
		
		if(strlen($request) > 0) {
			$args = explode(DIRECTORY_SEPARATOR, $request);
			
			if(count($args) > 0)
				$controller = array_shift($args);
			
			if(count($args) > 0)
				$view = array_shift($args);
			
			if(count($args) > 0) {
				foreach($args as $k => $v) {
					$args[$k] = urldecode($v);
				}
			}
		}

		if(!isset($controller)) {
			$controller = $config->default_controller;
			$args = array();
		}

		$controller = 'V7F\\Controller\\' . $controller;

		if(class_exists($controller)) {
			$active_controller = new $controller();
			$active_controller->$view($args);
		} else {
			trigger_error('Controller not found - ' . $controller, E_USER_ERROR);
		}
	}
}