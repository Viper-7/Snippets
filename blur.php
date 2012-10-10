<?php
$gd = imagecreatefromgif('http://www.php.net/images/php.gif');

gaussianBlur($gd);

header('Content-Type: image/gif');
imagegif($gd);

function gaussianBlur($gd, $direction = 90, $speed = 1.0, $bidirectional = TRUE) {
	$map = array(
		array(1, 0), 		// 0: top mid
		array(2, 0),		// 1: top right
		array(2, 1),  		// 2: mid right
		array(2, 2),		// 3: bottom right
		array(1, 2),		// 4: bottom mid
		array(0, 2),		// 5: bottom left
		array(0, 1),		// 6: mid left
		array(0, 0), 		// 7: top left
	);

	// Convert the 360 degree direction to a 0-7 direction index (float)
	$direction %= 360;
	$direction /= 45;
	
	// Create an empty convolution matrix
	$matrix = array_fill(0, 3, array(0.0, 0.0, 0.0));
	$matrix[1][1] = 1.0;
	
	// Find the starting index (the counter-clockwise side)
	$index = (int)$direction;
	
	// Calculate how much of the speed should be passed to the next direction
	$spread = fmod($direction, 1.0);

	// Counter-clockwise side gets the inverse of $spread
	list($x, $y) = $map[$index];
	$matrix[$x][$y] = $speed * (1.0 - $spread);

	if($bidirectional) {
		// Apply the same 180 degrees opposed
		list($x, $y) = $map[($index + 4) % 8];
		$matrix[$x][$y] = $speed * (1.0 - $spread);
	}
	
	// Clockwise side gets the rest from $spread
	list($x, $y) = $map[($index + 1) % 8];
	$matrix[$x][$y] = $speed * $spread;

	if($bidirectional) {
		// Apply the same 180 degrees opposed
		list($x, $y) = $map[($index + 5) % 8];
		$matrix[$x][$y] = $speed * $spread;
	}

	$divisor = array_sum(array_map('array_sum', $matrix));
	imageconvolution($gd, $matrix, $divisor, 0);
	
	return $gd;
}
