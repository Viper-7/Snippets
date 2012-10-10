<?php
namespace V7F\Template;

h2o::addTag('textbox');
class Textbox_Tag extends H2o_Node {
    public $name, $value;

    function __construct($argstring, $parser, $pos=0) {
        $argstring = trim($argstring);
	
	if(strpos($argstring, ' ') !== FALSE) {
		$arr = explode(' ', $argstring);
		$this->name = array_shift($arr);
		$this->value = implode(' ', $arr);
	} else {
		$this->name = $argstring;
		$this->value = '';
	}
    }

    function render($context, $stream) {
    	$editor = \V7F\Editor\Editor::getInstance();
	$value = $context->getVariable($this->value);
	$stream->write($editor->TextBox($this->name, $value));
    }
}
