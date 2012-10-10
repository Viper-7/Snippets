<!doctype html>
<html>
<head>
	<title>YouTube Viewer</title>
</head>
<body>
	<div>
		<?php
			$youtube = new YouTube();
			
			$query = isset($_GET['q']) ? $_GET['q'] : '';
			$startIndex = isset($_GET['start']) ? $_GET['start'] : 1;
			$numResults = isset($_GET['num']) ? $_GET['num'] : 9;
			
			$results = $youtube->search($query, $numResults, $startIndex, FALSE);
			
			foreach($results as $result)
			{
				echo $result->embed;
			}
			
			echo '<br/><div style="text-align: center">';
			echo '<a href="?q=' . urlencode($query) . '&amp;start=' . urlencode($startIndex - $numResults) . '&amp;num=' . $numResults . '">Prev Page</a>';
			echo ' &nbsp; ';
			echo '<a href="?q=' . urlencode($query) . '&amp;start=' . urlencode($startIndex + $numResults) . '&amp;num=' . $numResults . '">Next Page</a>';
			echo '</div>';
		?>
	</div>
</body>
</html>
<?php

class YouTube
{
	public function search($query, $numResults = 25, $startIndex = 1, $fetchToken = FALSE)
	{
		$results = "&max-results={$numResults}&start-index={$startIndex}";
		
		$searchResults = file_get_contents('http://gdata.youtube.com/feeds/api/videos?q=' . urlencode($query) . '&v=2&safeSearch=none' . $results);
		$xml = simplexml_load_string($searchResults);
		
		$out = array();
		
		foreach($xml->entry as $node)
		{
			$outNode = new StdClass();
			
			$outNode->title = (string)$node->title;
			foreach($node->link as $linkNode)
			{
				if($linkNode['rel'] == 'alternate')
				{
					$query = parse_url($linkNode['href'], PHP_URL_QUERY);
					parse_str($query, $linkData);
					$outNode->videoID = $linkData['v'];
				}
			}
			
			$out[] = $outNode;
		}
		
		if($fetchToken)
		{
			foreach($out as $key => $node)
			{
				$info = $this->getVideoInfo($node->videoID);
				$out[$key]->token = $info->token;
				$out[$key]->video = $this->getVideoURL($node->videoID, $info->token);
				$out[$key]->embed = $this->embedHTML($out[$key]);
			}
		} else {
			foreach($out as $key => $node)
			{
				$out[$key]->embed = $this->embedYouTubeFull($out[$key]);
			}
		}
		
		return $out;
	}
	
	public function getVideoInfo($videoID)
	{
		parse_str(file_get_contents("http://www.youtube.com/get_video_info?&video_id={$videoID}"), $videoInfo);
		
		return (object)$videoInfo;
	}
	
	public function getVideoURL($videoID, $token = NULL, $format = 18)
	{
		if(!$token)
		{
			$videoInfo = $this->getVideoInfo($videoID);
			$token = $videoInfo['token'];
		}
		
		$videoURL = "http://www.youtube.com/get_video?video_id={$videoID}&amp;t={$token}&amp;fmt={$format}";
		
		return $videoURL;
	}
	
	public function embedYouTubeMinimal($videoElement)
	{
		$url = "http://www.youtube.com/v/{$videoElement->videoID}?f=videos&app=youtube_gdata";
		
		$html = <<<EOI
<embed src="{$url}" width="50%" height="480"></embed>
EOI;
		
		return $html;
	}
	
	public function embedYouTubeFull($videoElement)
	{
		$html = <<<EOI
<object width="480" height="385"><param name="movie" value="http://www.youtube.com/v/{$videoElement->videoID}&amp;hl=en_US&amp;fs=1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/{$videoElement->videoID}&amp;hl=en_US&amp;fs=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed></object>
EOI;
		return $html;
	}
	
	public function embedHTML($videoElement)
	{
		$html = <<<EOI
<video width="640" height="360" controls autoplay>
	<source src="{$videoElement->video}"  type="video/mp4"  />

	<object width="640" height="384" type="application/x-shockwave-flash" data="player.swf">
		<param name="movie" value="player.swf" />
		<param name="flashvars" value="autostart=true&amp;file={$videoElement->video}" />
	</object>
</video>
<p>	<strong>Download Video:</strong>
	<a href="{$videoElement->video}">MP4</a>
</p>
EOI;
		return $html;
	}
}
?>