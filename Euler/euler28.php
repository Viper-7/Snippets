<?php
$a = 1;
$ainc = 2;
$total = 1;

for($x=1;$x<=2000;$x++) {
	$total += ($a += $ainc);
	if($x % 4 == 0) $ainc += 2;
}

echo $total;
