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
		self::$tokens = array();
		self::$tokenIndex = 0;
		self::$tagStack = array();

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
	
	/**
	  * Iterates backwards through the tag stack to find a container
	  * tag of the specified name 
	  **/
	public static function getContainer($name = NULL, $depth = 1)
	{
		// Rekey the tagStack array incase its gotten out of step
		self::$tagStack = array_values(self::$tagStack);
		
		end(self::$tagStack);
		$i = key(self::$tagStack);

		if($name === NULL)
		{
			while($depth > 1) { $depth--; prev(self::$tagStack); }
			return current(self::$tagStack[$i]);
		} else {
			while($depth > 0 && $i >= 0) {
				$elem = self::$tagStack[$i];
				
				// Loop up the tagStack looking for a tag with a matching name, until we reach the top
				while(( $ret = (!isset($elem->token['tag']) || $elem->token['tag'] != $name) ) && $i > 0)
				{
					--$i;
					$elem = self::$tagStack[$i];
				}

				--$depth;
				--$i;
			}
			
			// If no tags matched the specified name, return nothing
			if($ret)
				return NULL;
			
			return $elem;
		}
	}
	
	public function parse($tokens, $mode='runtime')
	{
		if($mode == 'admin')
			$fields = new FieldSet();
		if($mode == 'tags')
			$tags = array();

		self::$tokens = $tokens;
		ob_start();
		
		while(self::$tokenIndex < count($tokens))
		{
			if(isset($token)) unset($token);
			$token = $tokens[self::$tokenIndex];
			
			$tag = CFMLTag::createFromToken($token);
			
			if(!$tag)
				throw new CFML_Method_Exception("Tag not recognised: {$token['tag']}");

			if(($mode == 'admin' || $mode == 'tags') && $token['type'] == 'tag') {
				$token['type'] = 'admin';
				
				if($token['mode'] == 'end') {
					self::$tokenIndex++;
					continue;
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
						$tag->startTag = array_pop(self::$tagStack);
						$tag->tagScope =& $tag->startTag->tagScope;
						
						$tag->generatedContent = ob_get_clean();
						
						if($tag->startTag->name != $tag->name)
						{
							throw new CFML_Syntax_Exception("Tag nesting error - Expected </{$token['startTag']['tag']}> but found </{$token['tag']}>");
						}
						
						break;
				}
				
				$this->executeTag($tag, $token);
				
				if($tag->generatedContent)
					echo $tag->generatedContent;
			} elseif($token['type'] == 'admin') {
				if($mode == 'tags') {
					// Get all tags in the template for decoding by another script

					$tags[] = $tag;
				} else {
					// Get CMS form fields for all tags in the template
					
					$tagFields = $tag->getCMSFields();
	
					if($tagFields && $tagFields->Count()) {
						foreach($tagFields as $tagField) {
							$fields->push($tagField);
						}
					}
				}
			} else {
				preg_match_all('/#\s*(\w+)\s*#/', $token['text'], $matches, PREG_SET_ORDER);

				foreach($matches as $match)
				{
					$var = $match[1];

					$replacement = '';

					if(isset(CFML::$variables[$match[1]])) {
						$replacement =& CFML::$variables[$match[1]];
					} elseif(is_a(CFML::$fallback, 'ViewableData') && CFML::$fallback->hasMethod($var)) {
						$replacement = CFML::$fallback->$var();
					} elseif(is_a(CFML::$fallback, 'ViewableData') && CFML::$fallback->hasField($var)) {
						$replacement = CFML::$fallback->getField($var);
					}

					$token['text'] = str_replace($match[0], $replacement, $token['text']);
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
		
		if($mode == 'admin') {
			ob_end_clean();
			return $fields;
		} elseif($mode == 'tags') {
			ob_end_clean();
			return $tags;
		} else {
			return ob_get_clean();
		}
	}
	
	public function executeTag($tag, $token)
	{
		if($tag->attributes) {
			foreach($tag->attributes as $key => $value)
			{
				preg_match_all('/#\s*([^#]+)\s*#/', $value, $matches, PREG_SET_ORDER);

				foreach($matches as $match)
				{
					$var = $match[1];

					if(isset(CFML::$variables[$match[1]])) {
						$replacement =& CFML::$variables[$match[1]];
					} elseif(CFML::$fallback instanceof ViewableData && CFML::$fallback->hasMethod($match[1])) {
						$replacement = CFML::$fallback->$var();
					} elseif(CFML::$fallback instanceof ViewableData && CFML::$fallback->hasField($var)) {
						$replacement = CFML::$fallback->getField($var);
					} else {
						continue;
						//throw new CFML_Parser_Exception("Undefined variable {$match[1]}");
					}
					
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
				CFMLTag::$current_tag = $tag;
				return $tag->open();
			case 'end':
				$ret = $tag->close();
				CFMLTag::$current_tag = null;
				return $ret;
			case 'selfclosing':
				CFMLTag::$current_tag = $tag;
				$ret = $tag->execute();
				CFMLTag::$current_tag = null;
				return $ret;
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