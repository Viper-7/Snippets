<?php
$template = '[_B][_I]foo[/I][/B]';
$tags['_B'] = '<b>[_*]</b>';
$tags['_I'] = '<i>[_*]</i>';

$parser = new MyCode();
$tokens = $parser->tokenize($template);
echo $parser->parse($tokens, $tags);


class Token {
	public $content;
	public $type;
}

class MyCode {
	public function parse($tokens) {
		$content = '';
		$stack = array();
		
		foreach($tokens as $token) {
			switch($token->type) {
				case 'tag':
					$stack[] = array(
						'callback' => function($input) use($tags, $token) { return str_replace('[_*]', $input, $tags[$token->content]); },
						'content' => ''
					);
					break;
					
				case 'end_tag':
					$instruction = array_pop($stack);
					$func = $instruction['callback'];
					$content .= $func($instruction['content']);
					break;
					
				default:
					if($stack) {
						$instruction = array_pop($stack);
						$instruction['content'] .= $token->content;
						$stack[] = $instruction;
					} else {
						$content .= $instruction['content'];
					}
					break;
			}
		}
		
		return $content;
	}


	public function tokenize($code) {
		$index = 0;
		$tokens = array();
		$len = strlen($code);
		
		while($index < $len) {
			if(substr($code, $index, 2) == '[_') {
				$token = new Token();
				$token->type = 'tag';
				$index += 2;
				$startindex = $index;
				
				while(true) {
					if($code[$index] == ']') {
						$token->content = substr($code, $startindex, $index - $startindex);
					}
					if($index == $len) {
						throw new Exception('Unterminated [_..] tag');
					}
				}
				$tokens[] = $token;
			} else {
				$startindex = $index;
				while($index < $len) {
					if(substr($code, $index, 2) == '[_') {
						$index--;
						$token = new Token();
						$token->type = 'content';
						$token->content = substr($code, $startindex, $index - $startindex);
						$tokens[] = $token;
						continue 2;
					}
					$index++;
				}
			}
		}
		
		return $tokens;
	}
}