<pre><?php

if(!isset($_GET['start'])) $_GET['start'] = 0;

$gs = new GoogleSearch();
$result = $gs->search($_GET['q'], GoogleSearch::IMAGE, 54, $_GET['start']);
$i = 0;

print_r($result);
?>

<?php
class GoogleSearch {
	/****************************
	*	GoogleSearch::search()
	*
	*	@author		Viper-7 (viper7@viper-7.com)
	*	@date		2009-7-12
	*
	*	@param		search		String to search for
	*	@param		numresults	Number of results to return per search type (max 4)
	*	@param		searchType	Types to search for, can be a combination of several ie SEARCH_WEB | SEARCH_VIDEO | SEARCH_IMAGE
	*
	*	@return		Array of result objects
	*
	****************************/

	const WEB = 1;
	const LOCAL = 2;
	const VIDEO = 4;
	const IMAGE = 8;
	const BLOG = 16;
	const NEWS = 32;
	const BOOK = 64;
	const PATENT = 128;
	const AUTOCOMPLETE = 256;
	
	private $names = Array(
		self::WEB 		=> 'web',
		self::LOCAL 	=> 'local',
		self::VIDEO 	=> 'video',
		self::IMAGE 	=> 'image',
		self::BLOG 		=> 'blog',
		self::NEWS 		=> 'news',
		self::BOOK 		=> 'book',
		self::PATENT 	=> 'patent'
		);
	
	private function getNextHits($tag, $query, $start = 0)
	{
		$content = @file_get_contents('http://www.google.com/uds/G' . $tag . 'Search?v=1.0&safe=off&start=' . $start . '&q=' . rawurlencode($query));

		$result = @json_decode($content);
		
		return $result;
	}
	
	public function search($query, $searchType = self::WEB, $numresults = 10, $start = 0)
	{
		$out = array();
		
		if($searchType & self::AUTOCOMPLETE)
		{
			$content = file_get_contents('http://google.com/complete/search?q=' . rawurlencode($query));
			if($content)
			{
				preg_match('/window\.google\.ac\.h\((.+)\)/', $content, $match);

				$result = @json_decode($match[1]);
				
				$data = array_slice(array_pop($result),0,$numresults);
				
				foreach($data as $elem)
				{
					$subarr['text'] = trim($elem[0]);
					$subarr['results'] = str_replace(array(' results', ','), '', $elem[1]);
					$out['autocomplete'][] = (object)$subarr;
				}
			}
		}
		
		foreach($this->names as $key => $tag)
		{
			if($searchType & $key)
			{
				$out[$tag] = array();
				$count = 0;
				
				while($count < $numresults)
				{
					$result = $this->getNextHits($tag, $query, $start);
					
					if(!$result) break;
					
					if(isset($result->responseData)) {
						$out[$tag] = array_slice(array_merge($out[$tag], $result->responseData->results),0,$numresults);
					}
					
					$start += 4;
					$oldcount = $count;
					$count = count($out[$tag]);
					
					if($oldcount == $count) { break; }
				}
			}
		}
		
		return $out;
	}
}