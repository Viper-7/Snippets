<?php

function truncate_str($str, $maxchars, $truncated_by = '') {
	$str = trim($str);
	
	if(strlen($str) > $maxchars) {
		$endpos = strrpos($str, ' ', (strlen($str) - ($maxchars - strlen($truncated_by))) * -1);
		$str = substr($str, 0, $endpos) . $truncated_by;
	}

	return $str;
}

$str = 'abc def ghi jkl mno pqr stu vwx yz';

echo truncate_str($str, 11);
echo PHP_EOL;
echo truncate_str($str, 11, '...');