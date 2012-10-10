<?php
switch($thisTag['mode'])
{
	case 'start':
		break;
	case 'end':
		$sql = $thisTag['attributes']['generatedContent'];
		$thisTag['attributes']['generatedContent'] = '';
		echo 'SQL: ' . $sql . '<br/>';
		echo 'Params: ' . print_r($local['params'], TRUE) . '<br/>';
		break;
	case 'selfclosing':
		break;
}
