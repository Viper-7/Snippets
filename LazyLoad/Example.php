<?php
include 'Singleton.php';
include 'LazyLoad.php';

class Helpers extends LazyLoad {
	protected static $base_path = 'helpers';
}



$ranges = array(
	'192.168.1',
	'192.168.0',
	'59.167.245.132',
);

$ip = '192.168.1.25';

$matching_ranges = array_filter($ranges, Helpers::Filter()->searchByHaystack($ip));

var_dump($matching_ranges);