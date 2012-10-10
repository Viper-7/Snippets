<?php
switch($thisTag['mode'])
{
	case 'start':
		break;
	case 'end':
		break;
	case 'selfclosing':
		$parent =& CFMLParser::getParent();
		$parent['tagscope']['localscope']['params'][] = $thisTag['attributes']['value'];
		echo '?';
		break;
}
