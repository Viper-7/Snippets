<?php
include 'cfmlparser.php';
include 'cfmltag.php';
include 'tags/ssloop.php';

class CFML_Exception extends Exception {}
class CFML_Syntax_Exception extends CFML_Exception {}
class CFML_Method_Exception extends CFML_Exception {}
class CFML_Variable_Exception extends CFML_Exception {}
class CFML_Parser_Exception extends CFML_Exception {}

class CFML
{
	public static $variables = array();
	
	public function parseCFML($string)
	{
		$fp = fopen('php://temp', 'w+');
		fwrite($fp, $string);
		
		return $this->parseCFMLFile($fp);
		
		fclose($fp);
	}

	public function parseCFMLFile($file)
	{
		$parser = new CFMLParser();
		
		$tokens = $parser->tokenize($file);
		echo '<pre>';
		var_dump($tokens);
		echo '</pre>';
		$output = $parser->parse($tokens);

		return $output;
	}
}

$cfml = new CFML();
$output = $cfml->parseCFMLFile('/var/www/test.cfm');
echo $output;
