<?php
include 'SocketServer.php';

class ProxyListener extends SocketListener {
	protected $target;
	
	public function processData($client, $data) {
		$this->server->broadcast($data, $this->target);
	}
	
	public function setTarget($target) {
		$this->target = $target;
	}
}

$server = new SocketServer();
$id1 = $server->open(8002);
$id2 = $server->open(8003);


// Instantiate our listener and attach it to the server
$listener = new ProxyListener();
$server->addListener($listener, $id1);
$listener->setTarget($id2);


$listener = new ProxyListener();
$server->addListener($listener, $id2);
$listener->setTarget($id1);


// Run the server until killed
while($server->run()) {}