<?php
switch($tagMode)
{
	case 'start':
		echo 'Powerform_input Start';
		break;
	case 'end':
		echo 'Powerform_input End';
		break;
	case 'selfclosing':
		echo 'Powerform_input selfclosing';
		break;
}
