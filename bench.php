<?php
	/* Benchmarking Framework
	 * ----------------------------------------------------------------------------
	 * "THE BEER-WARE LICENSE" (Revision 42):
	 * <viper7@viper-7.com> wrote this file. As long as you retain this notice you
	 * can do whatever you want with this stuff. If we meet some day, and you think
	 * this stuff is worth it, you can buy me a beer in return.   Dale Horton
	 * ----------------------------------------------------------------------------
	 */

	function func1($invar) {
		return $invar;
	}

	function func2($invar) {
		return $invar;
	}
	
	function func3($invar) {
		return $invar;
	}
	
	
	
	// === Insert startup code here ===
	
	
	
	// === Configuration ===
	
	// Input variable to parse - or :
	// "rand(min, max)"	to generate random values on each iteration
	// "inc"		to increment a numerical value on each iteration
	$input = '';
	
	// Title of each test (array)
	$names = array('','');
	
	// Name of each function to test (array)
	$functions = array('func1','func2','func3');
	
	// Value to expect returned from the function (array or NULL)
	$expect = array('','','');
	//$expect = NULL;
	
	// Number of iterations to perform
	$iterations = 1000000;




?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><HEAD><TITLE></TITLE></HEAD>
<BODY>
<?php
	function benchCall($invar) {
		$var = "";
		return $var;
	}
	
	set_time_limit(0);
	$normalize = 1000;
	$iterations -= $iterations % $normalize;
	
	$rand = preg_match('/^rand\((\d+),(\d+)\)$/',$input,$matches);
	$inc = ($input == 'inc');
	
	echo 'Running Benchmark: This will take a few moments.<BR/><BR/>';

	if(is_array($expect)) {
		echo 'Parsing "';
		echo htmlentities(print_r($input,true));
		echo '" looking for "';
		echo htmlentities(print_r($expect[0],true));
		echo "\"<BR/><BR/>\n";
	} elseif($inc) {
		echo 'Parsing ';
		echo '0 - ' . $iterations;
		echo ' looking for equal values from each function<BR/><BR/>' . "\n";
	} else {
		echo 'Parsing ';
		echo htmlentities(print_r($input,true));
		echo ' looking for equal values from each function<BR/><BR/>' . "\n";
	}
	
	echo '<PRE>';
	
	for($z=0;$z<count($functions);$z++)
		$time[] = 0;

	$benchcall = 'benchCall';
	
	flush();
	ob_start();
	
	for($y=0;$y<$normalize;$y++) {
		$curtime = microtime(true);
		$val = $benchcall($input);
		$calltime = microtime(true) - $curtime;
		
		for($x=0;$x<($iterations / $normalize);$x++) {
			if($rand)
				$invar = rand($matches[1], $matches[2]);
			elseif($inc)
				$invar = ($y * ($iterations / $normalize)) + $x;
			else
				$invar = $input;
			
			foreach($functions as $key => $function) {
				$curtime = microtime(true);

				$out[$key] = $function($invar);
				
				$diff = microtime(true) - $curtime;
				$time[$key] += ($diff - $calltime);
				
				if((is_array($expect) && $out[$key] != $expect[$key]) || $out[$key] != $out[0]) { 
					ob_end_flush(); 
					echo 'Invalid Value from ' . $names[$key] . ' : ';
					var_dump($out[$key]);
					die('</PRE></BODY></HTML>');
				}
			}
		}
		
	}
	ob_end_clean();
	
	foreach($names as $key => $name) {
		echo str_pad(substr($name,0,32),32) . ': ' . str_pad(number_format($time[$key],4),6,' ',STR_PAD_LEFT) . ' Seconds';
		echo ' for ' . number_format($iterations,0,'.',',') . " Iterations\r\n";
	}
	
	if(is_array($expect)) echo '<BR/>All tests returned expected values.';
	else echo '<BR/>All functions returned matching values.';
	
	echo '</PRE>';
?>
</BODY></HTML>