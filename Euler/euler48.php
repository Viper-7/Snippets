<?php
	$total = 0;
	for($x=1;$x<=1000;$x++) $total = bcadd($total, bcpow($x, $x));
	echo substr($total, -10);