<?php
	function split_by_delims($str) {
		$args = func_get_args();
		
		$str = array_shift($args);
		
		reset($args);
		do {
			$pattern .= '(.*?)' . preg_quote(current($args), '/');
		} while(next($args));
		
		if(preg_match('/^' . $pattern . '$/', $str, $matches)) {
			array_shift($matches);
			reset($matches);
			do {
				$childargs = $args;
				array_unshift($args, current($matches));
				echo current($matches) . '<br>';
				$arr[] = call_user_func_array('split_by_delims', $childargs);
			} while(next($matches));
		} else {
			return $str;
		}
		return $arr;
	}
	
$str = 'sdfsdfAasdAxfcvBdfgsCBxcvxcCdfgdsAxcvBxcxcC';
echo $str . '<br>';
print_r(split_by_delims($str, 'A', 'B', 'C'));