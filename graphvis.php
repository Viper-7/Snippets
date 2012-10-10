<?php
	class GoogleChart
	{
		public $baseurl = 'http://chart.apis.google.com/chart';
		public $type = 'gv';
		public $size = array('width' => 300, 'height' => 200);
		public $nodes = array();
		public $nodeAttributes = array();
		
		public function __construct($size = NULL, $type = NULL)
		{
			if($size)
				$this->size = $size;

			if($type)
				$this->type = $type;
		}
		
		public function addNode($parentId, $id)
		{
			$parentId = preg_replace('/[^_0-9a-zA-Z\200-\377<>\'"=\/\-.:]+/u', ' ', $parentId);
			$id = preg_replace('/[^_0-9a-zA-Z\200-\377<>\'"=\/\-:.]+/u', ' ', $id);
			
			$this->nodes[$parentId][] = $id;
		}
		
		public function addNodeAttribute($id, $attribute, $value)
		{
			$id = preg_replace('/[^_0-9a-zA-Z\200-\377<>\'"=\/\-.:]+/u', ' ', $id);
			$attribute = preg_replace('/[^_0-9a-zA-Z\200-\377<>\'"=\/\-.:]+/u', ' ', $attribute);
			$value = preg_replace('/[^_0-9a-zA-Z\200-\377<>\'"=\/\-.:]+/u', ' ', $value);
			
			$this->nodeAttributes[$id][$attribute] = $value;
		}
		
		public function render()
		{
			$nodes = '';
			$nodeAttributes = '';
			
			foreach($this->nodes as $parentId => $children)
			{
				foreach($children as $id)
				{
					if($parentId)
						$nodes .= "\"{$parentId}\"->\"{$id}\";";
					else
						$nodes .= "\"{$id}\";";
				}
			}
			
			foreach($this->nodeAttributes as $id => $attributes)
			{
				$childNodeAttributes = array();
				
				foreach($attributes as $attribute => $value)
				{
					if(substr($value,0,1) == '<' && substr($value,-1,1) == '>')
						$childNodeAttributes[] = "\"{$attribute}\"={$value}";
					else
						$childNodeAttributes[] = "\"{$attribute}\"=\"{$value}\"";
				}
				
				$nodeAttributes .= "\"{$id}\" [" . implode(',', $childNodeAttributes) . "];";
			}
			
			$size = "{$this->size['width']}x{$this->size['height']}";
			
			$url = $this->baseurl . '?' . http_build_query(array(
				'cht' => $this->type
				,'chs' => $size
				,'chl' => "digraph{{$nodes}{$nodeAttributes}}"
				));
			
			echo "<img src=\"{$url}\">";
			// echo "<br/>" . urldecode($url);
		}
	}

	$gc = new GoogleChart(array('width' => 600, 'height' => 500));
	
<cf_graphviz size="200x350">
	<cf_graphviz_node id="Welcome Message" shape="box">
	<cf_graphviz_node id="Powerdata Form" shape="hexagon" style="filled" color="salmon">
	<cf_graphviz_node id="Reject Spam" shape="trapezium">
	<cf_graphviz_node id="Email Form Data" shape="folder" style="filled" color="lightblue2">
	<cf_graphviz_node id="Thankyou Message" shape="box">
	<cf_graphviz_node id="Redirect to Home Page" shape="box3d">
	
	<cf_graphviz_relation parent="Welcome Message" child="Powerdata Form">
	<cf_graphviz_relation parent="Powerdata Form" child="Email Form Data">
	<cf_graphviz_relation parent="Powerdata Form" child="Reject Spam">
	<cf_graphviz_relation parent="Email Form Data" child="Thankyou Message">
	<cf_graphviz_relation parent="Thankyou Message" child="Redirect to Home Page">
</cf_graphviz>

	
	$gc->addNode('Welcome Message', 'Powerdata Form');
	$gc->addNode('Powerdata Form', 'Email Form Data');
	$gc->addNode('Powerdata Form', 'Reject Spam');
	$gc->addNode('Email Form Data', 'Thankyou Message');
	$gc->addNode('Thankyou Message', 'Redirect to Home Page');
	
	$gc->addNodeAttribute('Welcome Message', 'shape', 'box');
	$gc->addNodeAttribute('Powerdata Form', 'shape', 'hexagon');
	$gc->addNodeAttribute('Powerdata Form', 'style', 'filled');
	$gc->addNodeAttribute('Powerdata Form', 'color', 'salmon');
	$gc->addNodeAttribute('Reject Spam', 'shape', 'trapezium');
	$gc->addNodeAttribute('Email Form Data', 'shape', 'folder');
	$gc->addNodeAttribute('Email Form Data', 'style', 'filled');
	$gc->addNodeAttribute('Email Form Data', 'color', 'lightblue2');
	$gc->addNodeAttribute('Thankyou Message', 'shape', 'box');
	$gc->addNodeAttribute('Redirect to Home Page', 'shape', 'box3d');
	
	$gc->render();
	