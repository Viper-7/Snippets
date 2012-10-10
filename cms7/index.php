<?php
	$GLOBALS['start'] = microtime(TRUE);
	
	include 'system/helpers/bootstrap.php';
	
	Config::set('base_path', __DIR__);
	Config::set('webroot', getWebserverPath(__DIR__));
	Config::set('profiler_enable', FALSE);
	
	Registry::set('db', new PDOV('orion', 'db', 'db', 'CMS7', 'mysql'));
	Registry::get('db')->log_queries = Config::get('profiler_enable');
	
	CMS7_Content_Cache::initCache(new CMS7_Memcached('localhost', '11211'));
	
	$controller = new Controller();
	$controller->route();
	
	include 'system/helpers/profiler.php';
?>