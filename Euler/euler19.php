<?php
date_default_timezone_set('Australia/Sydney');

$start 	= strtotime('1 jan 1902');
$end 	= strtotime('31 dec 2000');
$days 	= 0;

for($x=$start;$x<=$end;$x+= 86400) {
	if(strftime('%w', $x) == 0 && strftime('%e', $x) == 1) {
		$days++;
	}
}

$start 	= strtotime('1 jan 1906');
$end 	= strtotime('31 dec 1906');

for($x=$start;$x<=$end;$x+= 86400) {
	if(strftime('%w', $x) == 0 && strftime('%e', $x) == 1) {
		$days++;
	}
}

echo $days;
