<?php
date_default_timezone_set('Australia/Sydney');
session_start();
header('Refresh: 30');

if(!isset($_SESSION['input'])) $_SESSION['input'] = array();
if(!isset($_SESSION['shown'])) $_SESSION['shown'] = array();

$i = 0;

$unseen = array_diff($_SESSION['input'], $_SESSION['shown']);

if(count($unseen) < 20) {
	$contents = file_get_contents('http://live.lmgtfy.com/recent.json');
	$input = json_decode($contents);
	if(count($input) == 0) { echo 'Something bad happened...<br/>'; }
	foreach($input as $element) {
		$i++;
		if(array_search($element, $_SESSION['shown']) === FALSE &&
		   array_search($element, $unseen) === FALSE) {
			$_SESSION['input'][] = $element;
		}
	}
	if($i > 0) { $_SESSION['lastupdate'] = strtotime('now'); };
}

$input = $_SESSION['input'];
$toshow = array();
$i = 0;
reset($input);

do {
	$element = current($input);
	if(array_search($element, $_SESSION['shown']) === FALSE) {
		$toshow[] = $element;

		$_SESSION['shown'][] = $element;
		unset($_SESSION['input'][key($input)]);

		$i++;
	}
} while(next($input) && $i < 20);



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Let Me Google That For You</title>
	<link type="text/css" rel="stylesheet" href="/css/stylesheet.css"/> 
</head>
<body>
	<div class="vcenter" style="top: 15%">
		<h2 style="text-align: center;">lmgtfy.com - Let Me Google That For You - Live Feed</h2>
		<div class="center" style="width:350px;">
			<ul>
				<?php
					foreach($toshow as $show) {
						echo '<li><a href="http://www.google.com?q=' . urlencode($show) . '">';
						echo $show;
						echo "</a></li>\n";
					}
				?>
			</ul>
			<br/>
			<center><?php echo count($input); ?> Items remaining in cache.<br/>
			Last Update: <?php echo strftime('%c', $_SESSION['lastupdate']) ?>
			</center>
		</div>
	</div>
</html>