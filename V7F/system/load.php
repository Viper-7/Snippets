<?php
use V7F\Helpers\Registry, V7F\Helpers\ErrorHandler;

require 'autoload.php';
require 'helpers.php';

// Setup config registry
$config = Registry::getInstance();
$config->site_root = realpath(join_path(dirname(__FILE__),'..'));
require 'siteconfig.php';
unset($config);


// Setup error handler
$errorhandler = ErrorHandler::getInstance();
set_error_handler(array($errorhandler, 'error_handler'));
