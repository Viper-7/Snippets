<?php
function print_rr($var, $return = FALSE) {
	$str = '<pre>' . print_r($var, true) . '</pre>';
	
	if($return) {
		return $str;
	} else {
		echo $str;
	}
}

function join_path() {
	$args = func_get_args();
	
	foreach($args as $arg) {
		$targs[] = trim($arg,DIRECTORY_SEPARATOR);
	}
	
	return implode(DIRECTORY_SEPARATOR, $args);
}