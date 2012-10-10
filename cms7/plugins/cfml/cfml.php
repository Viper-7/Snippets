<pre><?php
include 'cfmlparser.php';

class CFML_Exception extends Exception {}
class CFML_Syntax_Exception extends CFML_Exception {}
class CFML_Method_Exception extends CFML_Exception {}
class CFML_Variable_Exception extends CFML_Exception {}
class CFML_Parser_Exception extends CFML_Exception {}

class CFML
{
	public static $variables = array();
	public $compilationTime = NULL;
	public $executionTime = NULL;
	
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
		
		$time = microtime(true);
		$tokens = $parser->tokenize($file);
		$this->compilationTime = number_format(microtime(true) - $time, 4);
		echo htmlentities(print_r($tokens,TRUE));
		$time = microtime(true);
		$output = $parser->parse($tokens);
		$this->executionTime = number_format(microtime(true) - $time, 4);

		return $output;
	}
}

$cfml = new CFML();
$output = $cfml->parseCFMLFile('/var/www/test.cfm');
echo $output;

echo "<br/>Compilation took: {$cfml->compilationTime} seconds.<br/>Execution took: {$cfml->executionTime} seconds.<br/>";
