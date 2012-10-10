<?php
/*
$code = <<<'EOI'
$chart = new Flowchart();
$http = $chart('HTTP Clients', array('shape' => 'box'));

$dns = $http->Amazon_DNS;
$load = $http->Load_Balancer('HTTP', array('shape' => 'box3d'));
$cdn = $http->Akamai_CDN;

$web = $load->Webserver('HTTP', array('color' => 'skyblue', 'style' => 'filled'));

$web->Database('SQL', array('color' => 'pink', 'style' => 'filled'));
$web->Fileserver('NFS', array('color' => 'greenyellow', 'style' => 'filled'));

$chart->render($http);
EOI;

eval($code);
*/

if(isset($_POST['graph']))
	eval($_POST['graph']);

class Flowchart
{
	public $baseurl = 'http://chart.apis.google.com/chart';
	public $type = 'gv';
	public $size = array('width' => 600, 'height' => 350);
	public $nodes = array();
	public $nodeAttributes = array();
	
	public function __construct($size = NULL, $type = NULL) {
		if($size)
			$this->size = $size;

		if($type)
			$this->type = $type;
	}
	
	public function createNode($title, $attributes = null) {
		return new FlowchartNode($this, $title, $attributes);
	}
	
	public function __call($title, $args) {
		$args += array(array());
		return $this->createNode($title, $args[0]);
	}
	
	public function __invoke($title, $attributes = null) {
		return $this->createNode($title, $attributes);
	}
	
	public function render($node) {
		$size = "{$this->size['width']}x{$this->size['height']}";
		
		$url = $this->baseurl . '?' . http_build_query(array(
			'cht' => $this->type
			,'chs' => $size
			,'chl' => 'digraph{' . $node->renderNode() . '}'
			));
		
		echo $url;
		//echo "<img src=\"{$url}\">";
		//echo "<br/>" . urldecode($url);
	}
}

class FlowchartNode {
	public $name;
	protected $title;
	protected $attributes = array();
	protected $links = array();
	protected $flowchart;
	protected $rendered = false;
	
	public function __construct($flowchart, $title, $attributes) {
		$this->flowchart = $flowchart;
		$this->title = $title;
		$this->name = preg_replace('/\W+/', '_', $title);
		if($attributes)
			$this->attributes = $attributes;
	}
	
	public function render() {
		return $this->flowchart->render($this);
	}
	
	public function renderNode() {
		if($this->rendered) return;
		$this->rendered = true;
		$attributes = "";
		foreach($this->attributes as $key => $value) {
			$attributes .= "{$key}=\"{$value}\"";
		}
		$out = "\"{$this->name}\" [label=\"{$this->title}\" " . $attributes . "];";
		
		foreach($this->links as $link) {
			$node = $link['node'];
			$out .= $node->renderNode();
			$out .= "\"{$this->name}\" -> \"{$node->name}\" [label=\"{$link['label']}\" {$link['attributes']}];";
		}
		
		return $out;
	}
	
	public function __get($node) {
		return $this->__call($node, array());
	}
	
	public function __call($node, $args) {
		if(is_string($node)) {
			$node = str_replace('_', ' ', $node);
		}
		array_unshift($args, $node);
		return call_user_func_array(array($this, '__invoke'), $args);
	}

	public function __invoke($node, $label='', $nodeAttributes = null, $joinAttributes = null) {
		if(!is_object($node)) {
			$node = $this->flowchart->createNode($node, $nodeAttributes);
		}
		$this->joinTo($node, $label, $joinAttributes);
		return $node;
	}
	
	public function joinTo($node, $label='', $joinAttributes) {
		$attributes = "";
		if($joinAttributes) {
			foreach($joinAttributes as $key => $value) {
				$attributes .= "{$key}=\"{$value}\"";
			}
		}
		$this->links[] = array('node'=>$node, 'label'=>$label, 'attributes'=>$attributes);
	}
}