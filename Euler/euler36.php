<pre>
<?php
for($x=0;$x<1000000;$x++) {
	if($x == strrev($x)) {
		$bin = decbin($x);
		if($bin == strrev($bin)) {
			$out[] = $x;
		}
	}
}

print_r($out);
echo array_sum($out);