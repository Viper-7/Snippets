<?php
class Layout
{
	public $layout;
	public $page;
	public $theme;
	public $vars;

	public function __construct($page_id)
	{
		$config = Config::getInstance();
		$this->page = $config->queries->layout->get_page_by_id($page_id);
		$this->layout = $config->queries->layout->get_page_layout($page_id, $this->page->layout_id);
		$this->theme = $config->queries->layout->get_theme_by_id($this->page->theme_id);
	}

	public function render()
	{
		$element = new LayoutElement($this->layout->positions[0]);
		$element->page = $this->page;
		$element->layout = $this->layout;
		$element->theme = $this->theme;
		return $element->render();
	}
}

class LayoutElement
{
	public $position;
	public $page;
	public $theme;

	public function __construct($position)
	{
		$this->position = $position;
	}

	public function render()
	{
		$config = Config::getInstance();
		$htmlhead = HTMLHead::getInstance();
		$template = new Template();

		$template->position = $this->position;

		if(isset($this->theme->assets->js))
			foreach($this->theme->assets->js as $js)
				$htmlhead->addAsset($js->url, HTMLHead::JS);

		if(isset($this->theme->assets->css))
			foreach($this->theme->assets->css as $css)
				$htmlhead->addAsset($css->url, HTMLHead::CSS)

		if(!isset($this->position->attributes->children))
			$this->position->attributes->children = array();

		if($this->position->childcount)
		{
			foreach($this->position->children as $child)
			{
				$childElement = new LayoutElement($child);
				$childElement->config = $config;
				$childElement->page = $this->page;
				$childElement->theme = $this->theme;

				$this->position->attributes->children[] = $childElement->render();
			}
		}
			
		$this->position->attributes->config = $config;
		$this->position->attributes->page = $this->page;
		$this->position->attributes->theme = $this->theme;

		return $template->render(
			join_path($config->module_path, $this->position->module_path, $this->position->module_output_path),
			$template->position->attributes);
	}
}