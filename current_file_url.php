<?php
$uri = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
$baseuri = '/' . trim(strpos($uri,'.') !== FALSE ? str_replace(basename($uri), '', $uri) : $uri,'/');
if(strlen($baseuri) > 1) $baseuri .= '/';

if(strlen(dirname(__FILE__)) > strlen(getcwd())) {
	$pathdiff = trim(str_replace(getcwd(), '', dirname(__FILE__)),'/');
	if(strlen($pathdiff) > 1) $pathdiff .= '/';
	$localuri = $baseuri . $pathdiff . basename(__FILE__);
} else {
	$pathdiff = trim(str_replace(dirname(__FILE__), '', getcwd()),'/');
	if(strlen($pathdiff) > 1) $pathdiff .= '/';
	$localuri = str_replace($pathdiff, '', $baseuri) . basename(__FILE__);
}

echo $localuri;