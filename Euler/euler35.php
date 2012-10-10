<?php
for($x=2;$x<=1000000;$x++) {
	if(!is_prime($x)) {
		continue;
	}

	$num = $x;
	$disp = "";
	
	for($y=1;$y<strlen($x);$y++) {
		$num = str_split($num);
		$digit = array_shift($num);
		array_push($num, $digit);
		$num = implode('', $num);
		
		if(!is_prime($num)) {
			continue 2;
		}

		$disp .= "$x - $num<br>";
	}
	
	echo "$disp$x = BINGO<br><br>";
	$out[] = $x;
}

echo count($out);