<?php
	function explodeNested($string, $delimiter = '\\', &$elem)
	{
		if(!$elem) $elem = array();
		
		$pos = strpos($string, $delimiter);
		if(!$pos) return $elem['value'] = $string;
		
		$key = substr($string, 0, $pos);
		$value = substr($string, $pos+1);
		
		if(!isset($elem[$key])) $elem[$key] = array();
		
		explodeNested($value, $delimiter, $elem[$key]);
	}
	
	$out = array();
	$arr = array('Foo\Bar', 'Foo\Bar\Baz', 'Foo\Baz');
	
	foreach($arr as $key => $line)
	{
		explodeNested($line, '\\', $out);
	}
	
	
	echo '<pre>';
	var_dump($out);