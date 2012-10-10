<?php
class CFMLParser
{
	public $starttag = array('<ss', '</ss');
	public $endtag = '>';
	
	public $suppressWhiteSpace = TRUE;
	
	public static $tokens = array();
	public static $tokenIndex = 0;
	public static $tagStack = array();

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
				$endtag = preg_quote($this->endtag, '/');
				
				while(!preg_match('/<(?:"[^"]*"|\'[^\']*\'|[^"\'' . $endtag . ']*)+' . $endtag . '/', $line, $matches)) {
					if(feof($fp))
					{
						throw new CFML_Parser_Exception("Unclosed tag at character {$linestart}");
					}
					
					$linestart = ftell($fp);
					$line .= fgets($fp, $chunksize);
					$readbytes += ftell($fp) - $linestart;
				}
				
				$endtok = strlen($matches[0])-1;
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
				
				$token = array('type' => 'tag', 'tag' => $tagname, 'attributes' => $attributes, 'mode' => $mode, 'tagcode' => $tagcode);
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
	
	public static function &getParent($name = NULL)
	{
		self::$tagStack = array_values(self::$tagStack);
		
		$i = count(self::$tagStack) - 1;
		
		if($name === NULL)
		{
			return self::$tagStack[$i];
		} else {
			$elem =& self::$tagStack[$i];
			while($elem->token['tag'] != $name && $i > 0)
			{
				--$i;
				$elem =& self::$tagStack[$i];
			}
			
			return $elem;
		}
	}
	
	public function parse($tokens, $mode='runtime')
	{
		if($mode == 'admin')
			$fields = new FieldSet();

		self::$tokens = $tokens;
		ob_start();
		
		while(self::$tokenIndex < count($tokens))
		{
			if(isset($token)) unset($token);
			$token = $tokens[self::$tokenIndex];
			
			$tag = CFMLTag::createFromToken($token);
			
			if(!$tag)
				throw new CFML_Method_Exception("Tag not recognised: {$token['tag']}");

			if($mode == 'admin') {
				if($token['type'] == 'tag') {
					$token['type'] = 'admin';
				}
			}
			
			if($token['type'] == 'tag') {
				switch($token['mode']) {
					case 'start':
						ob_start();
						
						$tag->startIndex = self::$tokenIndex;
						self::$tagStack[] = $tag;
						
						break;
						
					case 'end':
						$tag->parentTag = array_pop(self::$tagStack);
						$tag->tagScope =& $tag->parentTag->tagScope;
						
						$tag->generatedContent = ob_get_clean();
						
						if($tag->parentTag->name != $tag->name)
						{
							throw new CFML_Syntax_Exception("Tag nesting error - Expected </{$token['parenttag']['tag']}> but found </{$token['tag']}>");
						}
						
						break;
				}
				
				$this->executeTag($tag, $token);
				
				if($tag->generatedContent)
					echo $tag->generatedContent;
					
			} elseif($token['type'] == 'admin') {
				// Ghetto scaffolder
				
				$tagFields = $tag->getCMSFields();
				
				if($tagFields && $tagFields->Count()) {
					foreach($tagFields as $tagField) {
						$fields->push($tagField);
					}
				}
			
			} else {
				preg_match_all('/#\s*([^#]+)\s*#/', $token['text'], $matches, PREG_SET_ORDER);

				foreach($matches as $match)
				{
					if(isset(CFML::$variables[$match[1]]))
						$replacement =& CFML::$variables[$match[1]];
					else
						throw new CFML_Parser_Exception("Undefined variable {$match[1]}");
				
					$token['text'] = str_replace($match[0],$replacement,$token['text']);
				}
				
				if($this->suppressWhiteSpace)
				{
					echo trim($token['text']);
				} else {
					echo $token['text'];
				}
			}
			
			self::$tokenIndex++;
		}
		
		if($mode == 'admin')
			return $fields;
		else
			return ob_get_clean();
	}
	
	public function executeTag($tag, $token)
	{
		if($tag->attributes) {
			foreach($tag->attributes as $key => $value)
			{
				preg_match_all('/#\s*([^#]+)\s*#/', $value, $matches, PREG_SET_ORDER);

				foreach($matches as $match)
				{
					if(isset(CFML::$variables[$match[1]]))
						$replacement =& CFML::$variables[$match[1]];
					else
						throw new CFML_Parser_Exception("Undefined variable {$match[1]}");
					
					if($tag->attributes[$key] == $match[1])
					{
						$tag->attributes[$key] =& $replacement;
					} else {
						$tag->attributes[$key] = str_replace($match[0],$replacement,$tag->attributes[$key]);
					}
				}
			}
		}

		switch($token['mode']) {
			case 'start':
				return $tag->open();
			case 'end':
				return $tag->close();
			case 'selfclosing':
				return $tag->execute();
		}
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

	public static function gotoTag($tag)
	{
		ob_start();
		self::$tagStack[] =& $tag;
		self::$tokenIndex = $tag->startIndex;
	}
}