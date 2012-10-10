<?php
include 'DaisyDiff/HTMLDiff.php';

class DiffClass
{
	private $diff;
	
	public function __construct()
	{
		$this->diff = new HTMLDiffer();
	}
	
	public function diffString($from, $to)
	{
		return $this->diff->htmlDiff($from, $to);
	}
	
	public function diffURL($from, $to)
	{
		$from = str_replace('http://','',$from);
		$to = str_replace('http://','',$to);
		
		$from = file_get_contents('http://' . $from);
		$to = file_get_contents('http://' . $to);

		return $this->diff->htmlDiff($from, $to);
	}
}