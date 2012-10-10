<?php
switch($tagMode)
{
	case 'start':
		echo 'Powerform_ajax_success Start';
		break;
	case 'end':
		echo 'Powerform_ajax_success End';
		break;
	case 'selfclosing':
		echo 'Powerform_ajax_success selfclosing';
		break;
}
