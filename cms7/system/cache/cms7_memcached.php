<?php
class CMS7_Memcached
{
	protected $memcached;
	public $flag = NULL;
	public $expire = 900;
	
	public function __construct($host, $port)
	{
		$this->memcache = new Memcache();
		$this->memcache->connect($host, $port);
	}
	
	public function set($key, $content, $flag = NULL, $expire = NULL)
	{
		$flag = $flag ?: $this->flag;
		$expire = $expire ?: $this->expire;
		
		return $this->memcache->set($key, $content);
	}
	
	public function get($key)
	{
		return $this->memcache->get($key);
	}
	
	public function flush()
	{
		$this->memcache->flush();
	}
}