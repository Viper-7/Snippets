<?php
include 'SocketServer.php';

class ProxyListener extends SocketListener {
	public function processData($client, $data) {
		$client->broadcast($data);
	}
}

$server = new SocketServer();
$id1 = $server->open(8002);
$id2 = $server->open(8003);

$listener = new ProxyListener();
$server->addListener($listener);


// Run the server until killed
while($server->run()) {}