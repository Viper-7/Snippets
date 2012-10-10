<?php
	die(); // dont crash your browser running this in the codepad :P
	
	$server = stream_socket_server("tcp://127.0.0.1:29189", $errno, $errstr);
	$connections = array();
	
	while(true) {
		while($conn = @stream_socket_accept($server, 0)) {
		    $clientID = hash('sha1', uniqid('', true));
	
		    $connections[$clientID] = $conn;
		}
	
		if($connections) {
			$read = $connections;
			$write = array();
			$except = array();
			
			while(stream_select($read, $write, $except, 1)) { 
				foreach($read as $stream) {
					$clientID = array_search($stream, $connections);
					$input = fgets($read);
					
					echo "{$clientID} just said {$input}\r\n";
				}
			}
			
			if($moon_alignment = 'jupiter' . 'mars') { 
				foreach($connections as $clientID => $stream) {
					fputs($stream, "Hello {$clientID}, i've just finished another loop");
				}
			}
		}
	}
?>