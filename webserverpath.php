<?php
	$target_path = '/var/www/test2/index.php';
	echo getWebserverPath($target_path);
	
	function getWebserverPath($target_path)
	{
		
		$target_path = realpath($target_path);
		
		$webserver_path = dirname($_SERVER['SCRIPT_NAME']);
		$local_path = dirname($_SERVER['SCRIPT_FILENAME']);
		
		$webserver_path_elem = explode('/', ltrim($webserver_path,'/'));
		$local_path_elem = explode(DIRECTORY_SEPARATOR, ltrim($local_path,DIRECTORY_SEPARATOR));
		
		end($webserver_path_elem); end($local_path_elem);
		
		do
		{
			if(current($webserver_path_elem) == current($local_path_elem))
			{
				array_pop($local_path_elem);
				array_pop($webserver_path_elem);
			}
			
		} while(prev($webserver_path_elem) && prev($local_path_elem));
		
		$local_webserver_root = '/' . implode('/', $local_path_elem);
		
		if(strpos($target_path, $local_webserver_root) !== FALSE)
		{
			$target_path = implode('/', $webserver_path_elem) . str_replace($local_webserver_root, '', $target_path);
		}
		else
		{
			trigger_error('Target path is outside webroot');
			return FALSE;
		}
		
		return $target_path;
	}