<?php
switch($tagMode)
{
	case 'start':
		echo 'Powerform_submit Start';
		break;
	case 'end':
		echo 'Powerform_submit End';
		break;
	case 'selfclosing':
		echo 'Powerform_submit selfclosing';
		break;
}
