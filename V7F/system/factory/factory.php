<?php
namespace V7F\Factory;
use V7F\Helpers\Singleton, V7F\Helpers\DB;

abstract class Factory extends Singleton {
	protected $db;
	
	public function __construct() {
		$this->db = DB::getInstance();
	}
}
