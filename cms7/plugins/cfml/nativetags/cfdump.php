<?php
switch($thisTag['mode'])
{
	case 'start':
		break;
	case 'end':
		break;
	case 'selfclosing':
		if(TRUE)
		{
			print_r($thisTag['attributes']['var']);
		} else {
			throw new CFML_Variable_Exception("Undefined variable: {$thisTag['attributes']['var']}");
		}
		break;
}
