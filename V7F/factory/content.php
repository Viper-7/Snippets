<?php
namespace V7F\Factory;
use PDO;

class Content extends Factory {
	public function getContent($page = "main") {
		$stmt = $this->db->prepare('SELECT ID, Body, Page, Revision FROM Content WHERE Page = ? ORDER BY Revision DESC LIMIT 1');
		$stmt->execute(array($page));
		$pages = $stmt->fetchObjs('V7F\Model\Content');

		return $pages ? $pages[0] : FALSE;
	}
	
	public function getPages() {
		$stmt = $this->db->prepare('SELECT Page, Revision FROM Content ORDER BY Revision DESC');
		$stmt->execute();
		$pages = $stmt->fetchAll();
		
		return $pages;
	}
	
	public function savePage($page) {
		if(empty($page->ID)) {
			$stmt = $this->db->prepare('INSERT INTO Content (Body, Page, Revision) VALUES (?,?,?)');
			return $stmt->execute(array($page->Body, $page->Page, $page->Revision));
		} else {
			$stmt = $this->db->prepare('UPDATE Content (Body, Page, Revision) VALUES (?,?,?) WHERE ID=?');
			return $stmt->execute(array($page->Body, $page->Page, $page->Revision, $page->ID));
		}
	}
	
	public function deletePage($page) {
		if(!empty($page->ID)) {
			$stmt = $this->db->prepare('DELETE FROM Content WHERE ID=?');
			return $stmt->execute(array($page->ID));
		} else {
			return FALSE;
		}
	}
	
	public function save($pages) {
		$modified = 0;
		
		foreach($pages as $page) {
			$modified += $this->savePage($page);
		}
		
		return $modified;
	}
	
	public function delete($pages) {
		$modified = 0;

		foreach($pages as $page) {
			$modified += $this->deletePage($page);
		}
		
		return $modified;
	}
}