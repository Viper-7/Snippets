<?php
class HTMLHead extends Singleton
{
	private $tags;
	private $assets;

	const JS = 2;
	const CSS = 3;
	
	public function addAsset($url, $type)
	{
		$tag = NULL;
		
		switch($type)
		{
			case JS:
				$tag = "<script type='text/javascript' language='javascript' src='{$url}'/>\n";
				break;

			case CSS:
				$tag = "<link rel='stylesheet' href='{$url}'/>\n";
				break;
		}
		
		if(!$tag)
			throw new CMS7_Exception('Unhandled asset in HTMLHead: ' . $url);
		
		if(!in_array($this->tags, $tag))
		{
			$this->assets[$type][] = $url;
			$this->tags[] = $tag;
		}
	}
	
	public function outputHead()
	{
		echo implode($this->tags);
	}
}