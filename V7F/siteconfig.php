<?php
date_default_timezone_set('Australia/Sydney');

$config->web_root		=	'/var/www/aidaus.com';
$config->default_controller 	= 	'Content';

$config->show_errors 		= 	TRUE;
$config->error_log 		= 	'/var/log/aidaus.com/errors.log';
$config->error_page 		= 	'views/error.html';

$config->db_engine		=	'mysql';
$config->db_dbname 		= 	'aid';
$config->db_host 		= 	'orion';
$config->db_user 		= 	'db';
$config->db_pass 		= 	'db';
