<?php
class GPS_Listener extends SocketListener {
	public function processData($client, $data) {
		$input = substr($data, 1);
		switch($data[0]) {
			case "\x01":
				$client->setTag($input);
				break;
			case "\x02":
				$DeviceID = $client->getTag();
				DB::query("UPDATE locations SET position=? WHERE DeviceID=?", $input, $DeviceID);
				break;
		}
	}
}


// Start a server on port 8000
$server = new SocketServer(8000);

// Instantiate our listener and attach it to the server
$listener = new GPS_Listener();
$server->addListener($listener);

// Run the server until killed
while($server->run()) {}