<?php
switch($tagMode)
{
	case 'start':
		echo 'Powerform_section Start';
		break;
	case 'end':
		echo 'Powerform_section End';
		break;
	case 'selfclosing':
		echo 'Powerform_section selfclosing';
		break;
}
