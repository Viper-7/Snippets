<?php
	/*
	Let d(n) be defined as the sum of proper divisors of n (numbers less than n which divide evenly into n).
	If d(a) = b and d(b) = a, where a ? b, then a and b are an amicable pair and each of a and b are called 
	amicable numbers.

	For example, the proper divisors of 220 are 1, 2, 4, 5, 10, 11, 20, 22, 44, 55 and 110; therefore d(220) = 284.
	The proper divisors of 284 are 1, 2, 4, 71 and 142; so d(284) = 220.

	Evaluate the sum of all the amicable numbers under 10000.
	*/
	error_reporting(E_ALL & ~E_NOTICE);
	
	$input = range(1, 10000);

	$div = array();
	
	foreach($input as $a) {
		if(!isset($amicable[$a])) {
			for($x=1;$x<=$a/2;$x++) {
				if(bcmod($a, $x) == 0) {
					$div[$a] += $x;
				}
			}
		}
		if(isset($div[$div[$a]]) && $a == $div[$div[$a]] && $a != $div[$a]) {
			$amicable[$a] = $div[$a];
			$amicable[$div[$a]] = $div[$div[$a]];
		}
	}
	
	$total = 0;
	
	print_r($amicable);
	echo "\n\n";

	$amicable = array_keys($amicable);
	foreach($amicable as $val) {
		$total += $val;
	}
	
	echo $total;
