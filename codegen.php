<pre>
<?php
/**
*  
*
*
**/
$mask = 'abc^def';


$out = array('');

foreach(str_split($mask) as $char) {
	switch($char) {
		case '^':
			$codes = range('A','Z');
			break;
		case '$':
			$codes = range('a','z');
			break;
		case '#':
			$codes = range('0','9');
			break;
		default:
			$codes = array($char);
	}
	
	foreach($out as $key => $oldcode) {
		foreach($codes as $code) {
			unset($out[$key]);
			$out[] = $oldcode . $code;
		}
	}
	
	unset($codes);
}

foreach($out as $key => $code) {
		echo $code . "\n";
}
unset($codes);

?></pre>