<?php

class StringTemplate
{
	private $params;
	
	private function replacetag($tag)
	{
		if(isset($this->params[$tag[1]]))
		{
			return $this->params[$tag[1]];
		}
		
		return '';
	}
	
	public function render($string, $params)
	{
		$this->params = $params;
		
		return preg_replace_callback('/{{([^}]+)}}/', Array($this, 'replacetag'), $string);
	}
}