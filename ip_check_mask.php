<?php
	function ip_check_mask ($ip, $net_addr, $net_mask=null) {
		// process $net_addr = 192.168.0.0/16
		if(strpos($net_addr, '/') !== FALSE) {
			list($net_addr, $net_mask) = explode('/', $net_addr);
		}

		// process $net_mask = 255.255.255.0
		if(strpos($net_mask, '.') !== FALSE) {
			$long = ip2long($net_mask);
			$net_mask = 32-log(($long ^ -1)+1,2);
		}
		
		// 
		if($net_mask <= 0){ return false; }
	
		$ip_binary_string = sprintf("%032b",ip2long($ip));
		$net_binary_string = sprintf("%032b",ip2long($net_addr));
	
		return substr_compare($ip_binary_string, $net_binary_string, 0, $net_mask) === 0;
	}
	
	var_dump(ip_check_mask('192.168.1.2', '192.168.0.0/16'));

