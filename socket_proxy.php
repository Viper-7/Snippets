<?php
function open_port($port) {
	$socket = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);
	if (!$socket) {
		echo "Failed to open port $port: $errstr ($errno)\n";
	} else {
		echo "Opened port $port\n";
	}
	return $socket;
}

$sockets[] = open_port(8002);
$sockets[] = open_port(8003);

$connections = array();

while(TRUE) {
	$clients = $connections ? call_user_func_array('array_merge', $connections) : array();

	$read = array_merge($sockets, $clients);
	$write = $except = array();

	if(stream_select($read, $write, $except, 0, 1000)) {
		foreach($read as $socket) {
			$key = array_search($socket, $sockets);
			if($key !== FALSE) {
				$stream = stream_socket_accept($socket);
				$connections[$key][] = $stream;
				echo "Accepted connection to pool $key\n";
			}
			
			foreach($connections as $source => $clients) {
				$key = array_search($socket, $clients);
				
				if($key !== FALSE) {
					$data = fread($socket, 4096);
					$len = strlen($data);
					
					echo "Received $len bytes from client of pool $source\n";
					
					if($data === '' || $data === FALSE) {
						unset($connections[$source][$key]);
						continue;
					}
					
					$output = $connections;
					unset($output[$source]);
					$output = call_user_func_array('array_merge', $output);
					
					foreach($output as $out) {
						fwrite($out, $data);
					}
				}
			}
		}
	}
}
