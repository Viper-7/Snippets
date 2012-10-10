<?php
$url = 'http://www.google.com/basepages/producttype/taxonomy.en-US.txt';
$out = array();

foreach(file($url) as $line) {
	explodeDropdown($line, $out, ' > ');
}

drawTree($out);


function explodeDropdown($line, &$outarr, $delim = ',') {
	$parts = explode($delim, trim($line), 2);
	
	if(count($parts) == 2) {
		list($key, $value) = $parts;
		if(strpos($value, $delim)) {
			explodeDropdown($value, $outarr[$key]['children'], $delim);
		} else {
			$outarr[$key]['nodes'][] = $value;
		}
	}
}

function drawNode($node, $tag = 'ul') {
	foreach($node['nodes'] as $key) {
		echo '<li>' . $key;
		if(isset($node['children'][$key])) {
			echo "<$tag>";
			drawNode($node['children'][$key]);
			echo "</$tag>";
		}
		echo '</li>';
	}
}

function drawTree($tree, $tag = 'ul') {
	echo "<$tag>";
	foreach($tree as $node) {
		drawNode($node);
	}
	echo "</$tag>";
}