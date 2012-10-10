<?php
function cms7_autoload($class_name)
{
	$basepath = Config::get('base_path');
	$class_name = strtolower($class_name);

	$files = glob("{$basepath}/{modules,plugins,system}/*/{$class_name}.php", GLOB_BRACE);
	
	foreach($files as $file)
	{
		if(include_once($file))
			Config::set('loaded', array($file => time()));
	}
}

spl_autoload_register('cms7_autoload');