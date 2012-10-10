<?php
function numtowords($num) {
	$andneeded = FALSE;
	$out = array();
	
	if(($mul = floor($num / 1000)) > 0) {
		$out[] = numtowords($mul) . ' thousand';
		$andneeded = TRUE;
		$num -= $mul * 1000;
	}
	
	if(($mul = floor($num / 100)) > 0) {
		$out[] = numtowords($mul) . ' hundred';
		$andneeded = TRUE;
		$num -= $mul * 100;
	}
	
	$text = '';
	if($num > 19) {
		if($andneeded) { $out[] = 'and'; $andneeded = FALSE; }

		$mul = floor($num / 10) - 2;
		$names = array('twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety');
		$text = $names[$mul];
		$num -= ($mul + 2) * 10;
		if($num == 0) $out[] = $text;
	} elseif($num > 9) {
		if($andneeded) { $out[] = 'and'; $andneeded = FALSE; }

		$names = array('ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen');
		$mul = $num - 10;
		$out[] = $names[$mul];
		$num = 0;
	}
	
	if($num > 0) {
		if($andneeded) { $out[] = 'and'; $andneeded = FALSE; }

		$mul = $num - 1;
		$names = array('one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine');
		if($text) {
			$out[] = $text . '-' . $names[$mul];
		} else {
			$out[] = $names[$mul];
		}
	}
	
	return implode(' ', $out);
}

$text = '';
for($x=1;$x<=1000;$x++) {
	$word = numtowords($x);
	echo $word . '<br/>';
	$text .= str_replace(array(' ', '-'), '', $word);
	
}
echo strlen($text);
