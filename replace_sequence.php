<?php
$str = "hi foo, my name is foo too! what the foo?";

function replace_sequence($tag, $replace, $str) {
	$arr = explode($tag, $str);
	$out = '';
	
	$i=0;
	foreach($arr as $value) {
		$out .= $value;
		
		if(isset($replace[$i])) {
			 $out .= $replace[$i++];
		}
	}
	return $out;
}

echo replace_sequence('foo', array('bar', 'viper7', 'heck'), $str);