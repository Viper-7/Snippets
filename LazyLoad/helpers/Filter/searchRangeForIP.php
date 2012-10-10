<?php
return function($ip) {
	return function($range) use ($ip) {
		if(strpos($range, '/') !== FALSE) {
			list($net_addr, $size) = explode('/', $range, 2);
		    $ip_binary = sprintf("%032b",ip2long($ip));
			$net_binary = sprintf("%032b",ip2long($net_addr));
			return (substr_compare($ip_binary,$net_binary,0,$size) === 0); 
		} else {
			if(strpos($ip, $range) === 0) return true;
		}
	};
};