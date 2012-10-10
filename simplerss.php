<?php
$url = 'http://www.sitepoint.com/rss.php';

$dom = new DomDocument();

$dom->load($url);

$items = $dom->getElementsByTagName('item');

foreach($items as $item) {
	$title = $item->getElementsByTagName('title')->item(0);
	$link = $item->getElementsByTagName('link')->item(0);
	$description = $item->getElementsByTagName('description')->item(0);
	
	echo 'Title: ' . $title->nodeValue . '<br/>';
	echo 'Link: ' . $link->nodeValue . '<br/>';
	echo 'Description: ' . $description->nodeValue . '<br/>';
	echo '<br/>';
}