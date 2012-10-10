<?php
	/****************************
	*	getGoogleImage()
	*
	*	@author		Viper-7
	*	@date		2009-7-11
	*
	*	@param		search			String to search for
	*	@param		numresults		Number of results to return (max 4)
	*
	*	@return		An array of objects with the following properties:
	*		[width] => 600
	*		[height] => 450
	*		[imageId] => wLUInM5M4g_laM
	*		[tbWidth] => 135
	*		[tbHeight] => 101
	*		[unescapedUrl] => http://www.wwf.org.hk/images/hoihawan/gallery/seashore/2.-Sea-cucumber_ph.jpg
	*		[url] => http://www.wwf.org.hk/images/hoihawan/gallery/seashore/2.-Sea-cucumber_ph.jpg
	*		[visibleUrl] => www.thestudentroom.co.uk
	*		[title] => 2.-Sea-cucumber_ph.jpg
	*		[titleNoFormatting] => 2.-Sea-cucumber_ph.jpg
	*		[originalContextUrl] => http://www.thestudentroom.co.uk/showthread.php?t=601293&page=2
	*		[content] => Guys, how big are your <b>dicks</b>?
	*		[contentNoFormatting] => Guys, how big are your dicks?
	*		[tbUrl] => http://images.google.com/images?q=tbn:wLUInM5M4g_laM:www.wwf.org.hk/images/hoihawan/gallery/seashore/2.-Sea-cucumber_ph.jpg
	****************************/

	function getGoogleImage($search, $numresults = 1) {
		if($numresults > 4) $numresults = 4;

		$content = @file_get_contents('http://www.google.com/uds/GimageSearch?v=1.0&safe=off&q=' . rawurlencode($search));
		if(!$content) return FALSE;
		
		$result = json_decode($content);

		return array_slice($result->responseData->results,0,$numresults);
	}
	
		
	if ($argv[1] == "") {
		die("Usage: @image [1-4] search string\n");
	}
	
	$argv = explode(' ', $argv[1]);

	$num = 1;
	if(ctype_digit($argv[0])) {
		$num = array_shift($argv);
	}
	$search = implode(' ', $argv);

	$arr = getGoogleImage($search, $num);

	foreach($arr as $image) {
		echo $image->url . ' - ' . $image->width . 'x' . $image->height . '<br>';
	}

?>