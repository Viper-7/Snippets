<?php
namespace V7F\Controller;
use V7F\Helpers\Registry, V7F\Template\Template;

abstract class Controller {
	public $default_view;
	protected $template;
	protected $config;
	
	public function __construct() {
		$this->template = Template::getInstance();
		$this->config = Registry::getInstance();
	}
	
	public function __call($name, $args) {
		if($name == 'default') {
			if(isset($this->default_view)) {
				return $this->{$this->default_view}($args[0]);
			}
		}
	}
}
