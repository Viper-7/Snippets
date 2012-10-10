<?php
/*  Remove Method by Name
 *
 * Removes a named class method from a chunk of PHP source code, using the PHP
 * internal lexer.
 * 
 * 
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <viper7@viper-7.com> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return.   Dale Horton
 * ----------------------------------------------------------------------------
 */
 
$text = <<<'EOI'
<?php
class TestUnooo extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://stackoverflow.com/");
  }

  public function testMyTestCase()
  {
    $this->open("/questions/7556480/selenium-export-test-cases-as-php-phpunit-is-missing-in-my-selenium-ide-1-2-0");
    $this->click("link=Stack Overflow");
    $this->waitForPageToLoad("30000");
    $this->verifyTextPresent("RGB buffer to JPEG buffer");
    $this->click("link=RGB buffer to JPEG buffer");
    $this->waitForPageToLoad("30000");
  }
}
?>
EOI;

$out = removeMethodByName($text, 'setUp');

// Display the code we've produced
echo '<pre>' . htmlentities($out) . '</pre>';


function removeMethodByName($source, $methodName) {
	$tokens = token_get_all($source);
	$count = count($tokens);
	$out = '';
	$skipTo = 0;

	foreach($tokens as $index => $token) {
		// $token is now either a string, containing content the tokenizer didn't have an opcode for, or
		// an array, containing (opcode, content, [line])

		// Skip over elements that we know we want to
		if($index < $skipTo)
			continue;
			
		$start = $index;
		$found = false;

		// If we find a protected method, it might be setUp, so look deeper
		if(is_array($token) && $token[0] == T_PROTECTED) {
			$level = 0;
			
			// Loop until we hit the end of the input file (or we break out manually)
			while($index < $count) {
				$index++;
				
				// Keep track of opening and closing braces, so we know when the function is closed
				if($tokens[$index] == '{')
					$level++;
				
				if($tokens[$index] == '}') {
					$level--;

					// When we close the brace that was opened for this function, exit the while() loop
					if($level == 0) {
						// If we've found the named method, skip to the next block of code
						if($found) {
							// Move on to the token after }
							$index++;
							
							// Skip over any whitespace tokens after the method too
							while(is_array($tokens[$index]) && $tokens[$index][0] == T_WHITESPACE)
								$index++;
							
							// Store the index of the token to skip to
							$skipTo = $index;
						}
						
						// Exit the loop
						break;
					}
				}

				// Ignore any other content without an opcode
				if(!is_array($token)) {
					$out .= $token;
					continue;
				}
				
				// Capture the ${ opcode so we expect it's closing brace
				if($tokens[$index][0] == T_OPEN_TAG && $tokens[$index][1] == '${')
					$level++;
				
				// Sanity Check: We've hit a new method, bail out!
				if(in_array($tokens[$index][0], array(T_PROTECTED, T_PUBLIC, T_PRIVATE)))
					break;

				// If we find the named method, flag this method for removal
				if($tokens[$index][0] == T_STRING && $tokens[$index][1] == $methodName)
					$found = true;
			}
		}
		
		// If we didn't find the named method in this method block, store the code to be returned
		if(!$found) {
			if(is_array($token))
				$out .= $token[1];
			else
				$out .= $token;
		}
	}
	
	return $out;
}
