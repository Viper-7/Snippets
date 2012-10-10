<?php
namespace V7F\Controller;
use V7F\Factory as Factory, V7F\Model as Model;

class Content extends Controller {
	public $default_view = 'view';
	public $default_page = 'main';
	
	public function view($args) {
		if(empty($args)) $args = array($this->default_page);

		$content = Factory\Content::getInstance()->getContent($args[0]);

		$this->template->render('content', array('content' => $content));
	}

	public function edit($args) {
		$content = Factory\Content::getInstance()->getContent($args[0]);

		if(isset($_POST['body'])) {
			if(strpos($_SERVER['REMOTE_ADDR'],'192.168') === FALSE) {
				trigger_error('Disallowed', E_USER_ERROR);
			}
			
			$content->revise($_POST['body']);
			$content->save();
		}

		$this->template->render('edit', array('content' => $content));
	}
}