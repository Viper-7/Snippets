<?php
class GoogleFight
{
	public function fight()
	{
		$words = func_get_args();
		if (empty($words) || count($words) < 2) {
			return "Usage: !googlefight word1 word2\n";
		}
		
		$results = array();
		foreach($words as $word)
		{
			$result = json_decode(file_get_contents("http://ajax.googleapis.com/ajax/services/search/web?q=" . urlencode($word) . "&v=1.0"));
			$results[$word] = $result->responseData->cursor->estimatedResultCount;
		}

		$responses = array();
		foreach($results as $word => $result)
		{
			$responses[] = trim(urldecode($word)) . ' (' . number_format($result, 0) . ' hits)';
		}
		
		$message = implode(' vs ', $responses) . "\n";

		arsort($results);
		reset($results);
		
		return $message . '' . trim(urldecode(key($results))) . ' Wins!';
	}
}