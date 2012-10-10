<?php
	function htmlColorToDec($htmlcolor)
	{
		$out = Array('R' => 0, 'G' => 0, 'B' => 0);
		
		$htmlcolor = trim($htmlcolor, '#');
		
		if(!ctype_xdigit($htmlcolor)) return $out;
		
		if(strlen($htmlcolor) == 3) { 
			$arr = str_split($htmlcolor, 1);
			
			foreach($arr as &$color)
			{
				$color = str_repeat($color, 2);
			}
		}
		elseif(strlen($htmlcolor) == 6)
		{
			$arr = str_split($htmlcolor, 2);
		}
		else
		{
			return $out;
		}
		
		$arr = array_map('hexdec', $arr);

		$out['R'] = $arr[0];
		$out['G'] = $arr[1];
		$out['B'] = $arr[2];

		return $out;
	}
	
	$color = htmlColorToDec('#FFF');
	var_dump($color);
//	$img = imagecolorallocate($bg, $color['R'], $color['G'], $color['B']);