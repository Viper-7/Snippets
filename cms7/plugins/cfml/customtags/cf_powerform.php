<?php
switch($tagMode)
{
	case 'start':
		echo 'Powerform Start';
		break;
	case 'end':
		echo $tagCode;
		$tagCode = '';
		echo 'Powerform End';
		break;
	case 'selfclosing':
		echo 'Powerform selfclosing';
		break;
}
