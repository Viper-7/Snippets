<?php
function __autoload($class_name) {
	if(class_exists($class_name)) return;
	
	$namespace = explode('\\', $class_name);
	array_shift($namespace); // Shift off V7F
	
	$class_name = strtolower(array_pop($namespace));

	$path = strtolower(implode('/', $namespace));
	
	$class_files = glob("{{$path}/,system/$path/}{$class_name}.php", GLOB_BRACE);
	
	if(!empty($class_files)) {
		foreach($class_files as $class_file) {
			require $class_file;
		}
	} else {
		//debug_print_backtrace();
	}
}

