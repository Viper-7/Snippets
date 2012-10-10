<?php
class CFML_Exception extends Exception {}
class CFML_Syntax_Exception extends CFML_Exception {}
class CFML_Method_Exception extends CFML_Exception {}
class CFML_Variable_Exception extends CFML_Exception {}
class CFML_Parser_Exception extends CFML_Exception {}

class CFML
{
	public static $variables = array();
	public static $fallback = array();

	public static function parseCFML($string, $vars = array())
	{
		$fp = fopen('php://temp', 'w+');
		fwrite($fp, $string);
		
		return self::parseCFMLFile($fp, $vars);
		
		fclose($fp);
	}

	public static function parseCFMLFile($file, $vars = array())
	{
		self::$fallback = $vars;
		self::$variables = (method_exists($vars, 'getRecord') ? (array)$vars->getRecord() : (array)$vars);

		$parser = new CFMLParser();
		
		$tokens = $parser->tokenize($file);
		$output = $parser->parse($tokens);

		return $output;
	}

	public static function getCFMLFields($string) {
		$fp = fopen('php://temp', 'w+');
		fwrite($fp, $string);

		return self::getCFMLFieldsFile($fp);

		fclose($fp);
	}

	public static function getCFMLFieldsFile($file)
	{
		$parser = new CFMLParser();

		$tokens = $parser->tokenize($file);
		$output = $parser->parse($tokens, 'admin');

		return $output;
	}
	
	public static function getCFMLTags($string) {
		$fp = fopen('php://temp', 'w+');
		fwrite($fp, $string);

		return self::getCFMLTagsFile($fp);

		fclose($fp);
	}

	public static function getCFMLTagsFile($file)
	{
		$parser = new CFMLParser();

		$tokens = $parser->tokenize($file);
		$output = $parser->parse($tokens, 'tags');

		return $output;
	}	
}

