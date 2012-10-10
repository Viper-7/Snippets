<?php
	$duration = '1h m 49s';
	echo duration_to_seconds($duration);

	function duration_to_seconds($duration) {
		$modifiers = array(
			'd' => 86400,
			'h' => 3600, 
			'm' => 60, 
			's' => 1
		);

		$seconds = 0;

		foreach(explode(' ', $duration) as $segment) {
			if(($len = strlen($segment)) > 1) {
				list($num, $mod) = str_split($segment, $len-1);
			
				if(isset($modifiers[$mod]) && ctype_digit($num)) {
					$seconds += $num * $modifiers[$mod];
					continue;
				}
			}

			trigger_error('Unknown time specifier "' . $segment . '"', E_USER_ERROR);
		}

		return $seconds;
	}
?>
