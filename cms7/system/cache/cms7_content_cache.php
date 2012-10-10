<?php
class CMS7_Content_Cache extends Singleton
{
	protected $cache = NULL;
	
	public function setCache($cache)
	{
		return $this->cache = $cache;
	}
	
	public static function initCache($newCache)
	{
		$cache = self::getInstance();
		$cache->setCache($newCache);
	}
	
	public function get($key)
	{
		if($this->cache === NULL) return NULL;
		
		return $this->cache->get($key);
	}
	
	public function set($key, $content)
	{
		if($this->cache === NULL) return NULL;

		return $this->cache->set($key, $content);
	}
	
	public function flush()
	{
		if($this->cache)
			return $this->cache->flush();
	}
}