<?php

/**
* Simple rendering engine to embed variables into predefined strings
*
* Example:
* <code>
* $template = new StringTemplate();
*
* $params = Array('nick' => $user->nick, 'message' => $user->message);
* $message = '{{nick}} just said: {{message}}';
* 
* $this->send_msg( $template->render($message, $params) );
* </code>
*/
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
	
	/**
	* Renders the $params array into a $string template containing {{tags}}
	*
	* @param String Template content
	* @param Array Parameters to bind to tags in the template
	* @return String
	*/
	public function render($string, $params)
	{
		$this->params = $params;
		
		return preg_replace_callback('/{{([^}]+)}}/', Array($this, 'replacetag'), $string);
	}
}