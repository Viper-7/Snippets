<?php
class CFMLParser
{
	public $starttag = array('<cf', '</cf');
	public $endtag = '>';
	
	public $nativeTagPath = 'nativetags';
	public $customTagPath = 'customtags';
	public $suppressWhiteSpace = TRUE;
	
	public static $tokens = array();
	public static $tokenIndex = 0;
	public static $tagStack = array();

	public function parse($tokens)
	{
		self::$tokens = $tokens;
		
		ob_start();
		
		while(self::$tokenIndex < count($tokens))
		{
			if(isset($token)) unset($token);
			$token = $tokens[self::$tokenIndex];
			
			switch($token['type'])
			{
				case 'nativetag':
				case 'customtag':
					$func = $this->lookupTag($token['tag'], $token['type']);
					
					if($func)
					{
						if($token['mode'] == 'start')
						{
							ob_start();
							$token['tagscope'] = array('localscope' => array(), 'startindex' => self::$tokenIndex);
							self::$tagStack[] =& $token;
							$this->executeTag($func, $token, $tagscope);
						} elseif($token['mode'] == 'end') {
							$token['parenttag'] =& array_pop(self::$tagStack);
							$token['tagscope'] =& $token['parenttag']['tagscope'];
							
							$token['attributes']['generatedContent'] = ob_get_clean();
							
							if($token['parenttag']['tag'] != $token['tag'])
							{
								throw new CFML_Syntax_Exception("Tag nesting error - Expected </{$token['parenttag']['tag']}> but found </{$token['tag']}>");
							}
							
							$this->executeTag($func, $token);
							echo $token['attributes']['generatedContent'];
						} else {
							$token['tagscope'] = array('localscope' => array(), 'startindex' => self::$tokenIndex);
							self::$tagStack[] =& $token;
							$this->executeTag($func, $token);
							array_pop(self::$tagStack);
						}
					} else {
						throw new CFML_Method_Exception("Custom tag not recognised: {$token['tag']}");
					}
					break;
				
				case 'text':
					if($this->suppressWhiteSpace)
					{
						echo trim($token['text']);
					} else {
						echo $token['text'];
					}
					break;
			}
			
			self::$tokenIndex++;
		}
		
		return ob_get_clean();
	}
	
	public function tokenize($fp)
	{
		if(!is_resource($fp))
		{
			$was_resource = FALSE;
			
			if(is_scalar($fp) && is_readable($fp))
			{
				$fp = fopen($fp, 'r');
				if(!$fp) throw new CFML_Parser_Exception("Failed to open file - read error: {$fp}");
			} else {
				throw new CFML_Parser_Exception('Failed to open file - not a file or not readable: ' . print_r($fp, TRUE));
			}
		} else { 
			$was_resource = TRUE;
		}
		
		fseek($fp, 0);
		
		$tokens = array();
		$linestart = 0;
		$chunksize = 256;
		
		while(!feof($fp))
		{
			$linestart = ftell($fp);
			$line = fgets($fp, $chunksize);
			$readbytes = ftell($fp) - $linestart;
			
			if(($tok = $this->findTok($line, $this->starttag)) === 0)
			{
				// create $tag
				$endtok = strpos($line, $this->endtag);
				
				while($endtok === FALSE)
				{
					if(feof($fp))
					{
						throw new CFML_Parser_Exception("Unclosed tag at character {$linestart}");
					}
					
					$linestart = ftell($fp);
					$line .= fgets($fp, $chunksize);
					$readbytes += ftell($fp) - $linestart;
					
					$endtok = strpos($line, $this->endtag);
				}
				
				$wholetag = trim(substr($line, $tok+1, $endtok-1));
				
				
				
				$mode = 'start';
				if(substr($wholetag, 0, 1) == '/')
				{
					$mode = 'end';
					$wholetag = trim(substr($wholetag, 1));
				} elseif(substr($wholetag, -1) == '/')
				{
					$mode = 'selfclosing';
					$wholetag = trim(substr($wholetag, 0, -1));
				}
				
				$tok = strpos($wholetag, ' ');
				$attributes = NULL;
				if($tok)
				{
					$tagname = substr($wholetag, 0, $tok);
					$attributes = substr($wholetag, $tok+1);
					
					preg_match_all('/(\b\w+\b)\s*=\s*(?:"([^"]*)"|\'([^\']*)\')+/i', $attributes, $matches, PREG_SET_ORDER);
					
					$attributes = array();
					foreach($matches as $match)
					{
						$attributes[$match[1]] = $match[2];
					}
				} else {
					$tagname = $wholetag;
				}
				
				$tagcode = trim(substr($wholetag, strlen($tagname)));
				
				fseek($fp, (-1 * $readbytes) + $endtok + 1, SEEK_CUR);
				
				if(substr($wholetag,2,1) == '_') $type = 'customtag'; else $type = 'nativetag';
				
				$token = array('type' => $type, 'tag' => $tagname, 'attributes' => $attributes, 'mode' => $mode, 'tagcode' => $tagcode);
				$tokens[] = $token;
				continue;
			} else {
				while($tok === FALSE)
				{
					if(feof($fp))
					{
						$tok = strlen($line);
						
						if($tok == 0)
						{
							break 2;
						} else {
							break;
						}
					}

					$linestart = ftell($fp);
					$line .= fgets($fp, $chunksize);
					$readbytes += ftell($fp) - $linestart;
					
					$tok = $this->findTok($line, $this->starttag);
				}

				fseek($fp, (-1 * $readbytes) + $tok, SEEK_CUR);
				$token = array('type' => 'text', 'text' => substr($line, 0, $tok));
				$tokens[] = $token;
				continue;
			}
		}

		if(!$was_resource) fclose($fp);
		
		return $tokens;
	}
	
	public static function gotoToken($token)
	{
		ob_start();
		self::$tagStack[] =& $token;
		self::$tokenIndex = $token['tagscope']['startindex'];
	}
	
	public static function &getParent($name = NULL)
	{
		self::$tagStack = array_values(self::$tagStack);
		
		$i = count(self::$tagStack) - 1;
		
		if($name === NULL)
		{
			return self::$tagStack[$i];
		} else {
			$elem =& self::$tagStack[$i];
			while($elem['token']['tag'] != $name && $i > 0)
			{
				--$i;
				$elem =& self::$tagStack[$i];
			}
			
			return $elem;
		}
	}
	
	public function executeTag($tagPath, &$thisTag)
	{
		$local =& $thisTag['tagscope']['localscope'];
		$variables =& CFML::$variables;
		
		foreach($thisTag['attributes'] as $key => $value)
		{
			preg_match_all('/#\s*([^#]+)\s*#/', $value, $matches, PREG_SET_ORDER);

			foreach($matches as $match)
			{
				$replacement = eval("return {$match[1]};");
				
				if($thisTag['attributes'][$key] == $match[1])
				{
					$thisTag['attributes'][$key] =& $replacement;
				} else {
					$thisTag['attributes'][$key] = str_replace($match[0],$replacement,$thisTag['attributes'][$key]);
				}
			}
		}
		
		include $tagPath;
	}
	
	public function addCustomTagClass($class)
	{
		$this->classes[] = $class;
	}
	
	private function findTok($haystack, $needles)
	{
		$toks = array();
		
		foreach($needles as $needle)
		{
			$tok = strpos($haystack, $needle);
			if($tok !== FALSE)
			{
				$toks[] = $tok;
			}
		}
		
		if(count($toks))
			return min($toks) ?: 0;
		else
			return FALSE;
	}
	
	private function lookupTag($method, $type)
	{
		if($type == 'nativetag')
		{
			$path = $this->nativeTagPath . '/' . $method . '.php';
			
			if(file_exists($path))
			{
				return $path;
			}
		} elseif($type == 'customtag') {
			$path = $this->customTagPath . '/' . $method . '.php';

			if(file_exists($path))
			{
				return $path;
			}
		}
		
		return FALSE;
	}
	
	private function &lookupVar($var)
	{
		foreach($scopes as $scope)
		{
			if(isset($scope[$var]))
			{
				return $scope[$var];
			}
		}
		
		return $return = FALSE;
	}
}