<?php
include 'SocketServer.php';

class HTTPListener extends SocketListener {
	protected $partial = array();
	
	public function recvData($clientID, $data, $serverID = NULL) {
		if(isset($this->partial[$clientID])) {
			$request = $this->partial[$clientID];
			unset($this->partial[$clientID]);
			$request['body'] = $data;
		} else {
			$headers = explode("\r\n", $data);
			$status = array_shift($headers);
			
			list($request['method'], $request['path'], $request['http']) = explode(' ', $status);
			
			foreach($headers as $key => $value) {
				list($header, $content) = explode(':', $value, 2);
				$request['headers'][$header] = trim($content);
			}
			
			if(!in_array($request['method'], array('GET', 'HEAD'))) {
				$this->partial[$clientID] = $request;
				return;
			}
		}
		
		parent::recvData($clientID, $request, $serverID);
	}
	
	public function processData($client, $data) {
		$client->send("HTTP/1.1 200 OK\r\nContent-Type: text/html\r\n\r\nHello, World!<br>You Requested {$data['path']}\r\n\r\n");
		$client->disconnect();
	}
}

$server = new SocketServer();
$server->setDelimiters("\r\n\r\n");
$id1 = $server->open(8002);

$listener = new HTTPListener();
$server->addListener($listener);



// Run the server until killed
while($server->run()) {}