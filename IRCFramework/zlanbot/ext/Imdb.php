<?php
class Imdb
{
	public $channel;

	public function searchIMDB($search_string)
	{
		$data = file_get_contents("http://www.google.com.au/search?btnI=1&q=" . urlencode($search_string) . "+site%3Aimdb.com");

		preg_match('#<title>(.+?)</title>#is', $data, $title);
		preg_match('#<div class="info">\s*<h5>Release Date:</h5>(.+?)<#is', $data, $release_date);
		preg_match('#<div class="info">\s*<h5>Genre:</h5>(.+?)</div>#is', $data, $genre);
		preg_match('#<div class="info">\s*<h5>Tagline:</h5>(.+?)<#is', $data, $tagline);
		preg_match('#<div class="info">\s*<h5>Plot:</h5>(.+?)<#is', $data, $plot);
		preg_match('#<div class="info"[^>]*>\s*<h5>User Rating:</h5>.+?<b>(.+?)</b>#is', $data, $user_rating);
		
		$out = new StdClass;
		
		if(!empty($title))
		{
			if(strpos($title[1],'site:imdb.com') !== FALSE)
			{
				return FALSE;
			}
			else
			{
				$out->title = trim(str_replace(array("\r", "\n"), array('',''), html_entity_decode($title[1])));
			}
		}
		else
		{
			$out->title = '(none)';
		}

		if(!empty($release_date))
			$out->release_date = trim(str_replace(array("\r", "\n"), array('',''), html_entity_decode($release_date[1])));
		else
			$out->release_date = '(none)';

		if(!empty($genre))
			$out->genre = trim(html_entity_decode(str_replace(array("\r", "\n"), array('',''), str_replace(array('more',' | '), array('', ', '), strip_tags($genre[1])))));
		else
			$out->genre = '(none)';

		if(!empty($tagline))
			$out->tagline = trim(str_replace(array("\r", "\n"), array('',''), html_entity_decode($tagline[1])));
		else
			$out->tagline = '(none)';

		if(!empty($plot))
			$out->plot = trim(str_replace(array("\r", "\n"), array('',''), html_entity_decode($plot[1])));
		else
			$out->plot = '(none)';

		if(!empty($user_rating))
			$out->user_rating = trim(str_replace(array("\r", "\n", '/10'), array('','',''), html_entity_decode($user_rating[1])));
		else
			$out->user_rating = '(none)';
		
		return $out;
	}
}
