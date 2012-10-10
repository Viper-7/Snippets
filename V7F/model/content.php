<?php
namespace V7F\Model;

class Content extends Model {
	public $ID, $Body, $Page, $Revision;
	
	public function revise($body) {
		$this->ID = NULL;	// Clear ID to insert a fresh record
		$this->Body = $body;
		$this->Revision++;
	}
}