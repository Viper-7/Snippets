<?php
class Controller
{
	public $runlevel; // 0 stopped, 1 starting, 2 pre-cache, 3 cache miss, 4 output, 5 debug, 6 shutting down
	public $layout;
	public $page_id;
	public $site_details;

	public function __construct()
	{
		$this->runlevel = 1;
	}

	public function startup()
	{
		$this->runlevel = 2;
	}

	public function route()
	{
		if($this->runlevel == 1) $this->startup();

		$cache = CMS7_Content_Cache::getInstance();

		$vars = $this->getURIVars();
		if(isset($vars['flushCache']))
		{
			$cache->flush();
		}
		
		$key = $this->cacheGetKey();
		
		if(!$content = $this->cacheGet($key))
		{
			$this->runlevel = 3;
			$content = $this->renderRoute((object)$vars);
			$this->cacheSet($key, $content);
		}
		
		$this->runlevel = 4;
		echo $content;
		
		Stats::logRequest();
	}
	
	public function cacheGetKey()
	{
		return md5(serialize(array($_GET, $_POST)));
	}
	
	public function cacheSet($key, $content)
	{
		$cache = CMS7_Content_Cache::getInstance();
		
		return $cache->set($key, $content);
	}
	
	public function cacheGet($key)
	{
		$cache = CMS7_Content_Cache::getInstance();
		
		return $cache->get($key);
	}
	
	public function renderRoute($vars)
	{
		$config = Config::getInstance();
		$queries = CMS7_Queries::getInstance();

		$config->queries = $queries;
		$config->theme_path = join_path($config->base_path, 'themes');
		$config->module_path = join_path($config->base_path, 'modules');
		$config->plugin_path = join_path($config->base_path, 'plugin');

		$config->request_vars = $vars;
		$config->site = $config->queries->site->get_site_details();
		$this->site = $config->site;
		
		if(isset($vars->page))
		{
			if(ctype_digit($vars->page))
			{
				$page_id = $vars->page;
			} else {
				$page_id = $config->queries->site->find_page_id_by_name(strtolower($vars->page));
			}
		}

		if(!isset($page_id) || !$page_id)
		{
			$page_id = $this->site->site_home_page_id;
		}

		$this->page_id = $page_id;
		
		$layout = new Layout($page_id);
		$this->layout = $layout;
		$layout->vars = $vars;
		$content = $layout->render();
		
		return $content;
	}

	protected function getURIVars()
	{
		$baseurl = getWebserverPath('.');
		$url = trim(substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $baseurl) + strlen($baseurl)), '/');
		$parts = explode('/', $url);
		$vars = array();

		switch(count($parts))
		{
			case 0:
				return $_GET;
			case 1:
				return array_merge($_GET, array('page' => $parts[0]));
			default:
				$vars['page'] = array_shift($parts);
		}

		$parts = array_chunk($parts, 2);

		foreach($parts as $part)
		{
			if($part[0])
			{
				$part += array(NULL, NULL);
				$vars[$part[0]] = $part[1];
			}
		}

		$vars = array_merge($vars, $_GET);

		return $vars;
	}
}