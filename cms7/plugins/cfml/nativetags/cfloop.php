<?php
switch($thisTag['mode'])
{
	case 'start':
		if(!isset($thisTag['attributes']['from']))
			throw new CFML_Syntax_Exception('cfloop: from attribute is required.');
		if(!isset($thisTag['attributes']['to']))
			throw new CFML_Syntax_Exception('cfloop: to attribute is required.');
		if(!isset($thisTag['attributes']['index']))
			throw new CFML_Syntax_Exception('cfloop: index attribute is required.');
		if(!isset($thisTag['attributes']['step']))
			$thisTag['attributes']['step'] = 1;
		
		eval("{$thisTag['attributes']['index']} = {$thisTag['attributes']['from']};");
		break;
	case 'end':
		$index = eval("return {$thisTag['parenttag']['attributes']['index']} += {$thisTag['parenttag']['attributes']['step']};");

		if(empty($local['lastpass']))
		{
			if($index != $thisTag['parenttag']['attributes']['to'])
			{
				CFMLParser::gotoToken($thisTag['parenttag']);
				break;
			}
			
			$local['lastpass'] = TRUE;
			CFMLParser::gotoToken($thisTag['parenttag']);
		}
		
		break;
	case 'selfclosing':
		break;
}
