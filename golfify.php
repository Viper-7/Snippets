<?php
$infile = 'test.php';

$incode = file_get_contents($infile);
$nextletter = 'a';

$replace = array('#//.*?$#m' => '',
		'#/\*.*?\*/#s' => '',
		'/<\?php\s+/' => '<?',
		'/(echo|return|function|class)\s/i' => '$1§');

$incode = preg_replace(array_keys($replace), array_values($replace), $incode);
	
for($x=0;$x<100;$x++) {
	$incode = preg_replace('/["\']([^"\'\n]*) ([^"\'\n]*)["\']/m',"'$1§$2'",$incode);
}
$incode = preg_replace('/\s+/','',$incode);
$incode = str_replace('§', ' ', $incode);

while(preg_match('/(\$\w{2,})/', $incode, $matches)) {

	while(strpos($incode, '$'.$nextletter) !== FALSE) {
		if($nextletter == 'z') { $nextletter = 'A'; } else { $nextletter = chr(ord($nextletter)+1); }
	}
	
	$incode = str_replace($matches[1], '$' . $nextletter, $incode);

	if($nextletter == 'z') { $nextletter = 'A'; } else { $nextletter = chr(ord($nextletter)+1); }
}

echo '<pre>' . htmlentities($incode);
