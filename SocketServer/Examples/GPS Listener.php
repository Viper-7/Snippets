<?php
include 'SocketServer.php';

class GPS_Listener extends SocketListener {
	public function processData($client, $data) {
		switch($data[0]) {
			case "\x01":
				$client->setTag(substr($data, 1));
				break;
			case "\x02":
				$DeviceID = $client->getTag();
				DB::query("UPDATE locations SET position=? WHERE DeviceID=?", $data, $DeviceID);
				break;
		}
	}
}

$server = new SocketServer();
$id1 = $server->open(8001);

$listener = new GPS_Listener();
$server->addListener($listener);

while($server->run()) {}