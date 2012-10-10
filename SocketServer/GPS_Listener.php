<?php
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