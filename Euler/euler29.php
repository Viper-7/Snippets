<pre>
<?php
set_time_limit(120);

$out = array();
for($x=2;$x<=100;$x++) {
	for($y=2;$y<=100;$y++) {
		$out[] = bcpow($x, $y);
	}
}

$out = array_unique($out);
echo count($out);