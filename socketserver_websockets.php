<?php
/** START EXAMPLE CODE **/

	class MyWebSocket extends WebSocketListener {
		public $origin = 'http://home.viper-7.com';
		public $location = 'ws://home.viper-7.com:8000/';

		public $pollFrequency = 5000;
		public $debug = TRUE;
		
		public function processData($clientID, $data) {
			$this->broadcast(date('[H:m:i] ') . $data);
		}
	}

	// Start a server on port 8000
	$server = new SocketServer(8000);

	// Instantiate our listener and attach it to the server
	$listener = new MyWebSocket();
	$server->addListener($listener);

	// Run the server until killed
	while($server->run()) {}

/** END EXAMPLE CODE **/



abstract class WebSocketListener implements ISocketListener {
	public $origin;
	public $location;
	public $debug = false;
	public $pollFrequency = 1000;
	public $nonces = array();
	public $keys = array();
	public $serverTypes = array();
	public $allowBasic = true;
	
	protected $server;
	
	/**
	* Callback method to process the headers of a new client connection
	*
	* Override this to provide your functionality
	*
	* @param string Client Identifier
	* @param string Headers received from the client, separated by \r\n
	* @param string Webservice URL the client used to connect
	* @param string HTTP version from the client
	*/
	public function processHeaders($clientID, $headers, $url, $http) {
	
	}

	/**
	* Callback method to process any incoming packet of data from the client
	*
	* Override this to provide your functionality
	*
	* @param string Client Identifier
	* @param string Data received from the client (in UTF-8 encoding)
	*/
	public function processData($clientID, $data) {
	
	}

	/**
	* Callback method called once every pollFrequency milliseconds
	*
	* Override this to perform any server-side tasks to check for new data
	* to send to the client.
	*/
	public function poll() {}

	/**
	* Internal callback method called by the SocketServer to process any incoming data
	*
	* @param string Client Identifier
	* @param string Raw packet of data, containing either a set of headers or a 
	*			    websocket packet
	*/
	public function recvData($clientID, $data) {
		// Process the header if this is the first message from a client
		if(preg_match('/^(?:GET|OPTIONS) ([^ ]+) (.+?)\r\n/', $data, $matches)) {
		
			list(, $url, $http) = $matches;
			$headers = explode("\r\n", trim($data));
			foreach($headers as $index => $line) {
				list($name, $value) = explode(": ", $line, 2) + array('','');
				unset($headers[$index]);
				$headers[$name] = $value;
			}
			
			switch(TRUE) {
				case isset($headers['Sec-WebSocket-Key1']) && isset($headers['Sec-WebSocket-Key2']) && strlen($this->server->inputBuffer[$clientID]) >= 8:
					$nonce = substr($this->server->inputBuffer[$clientID], 0, 8);
					$this->server->inputBuffer[$clientID] = substr($this->server->inputBuffer[$clientID], 8);
					$this->serverTypes[$clientID] = 1;
					
					if($this->debug) echo "Received secure1 headers from {$clientID}\n";
					
					$numbers1 = preg_replace('/\D+/','', $headers['Sec-WebSocket-Key1']);
					$numbers2 = preg_replace('/\D+/','', $headers['Sec-WebSocket-Key2']);
					$spaces1 = strlen(preg_replace('/[^ ]+/','', $headers['Sec-WebSocket-Key1']));
					$spaces2 = strlen(preg_replace('/[^ ]+/','', $headers['Sec-WebSocket-Key2']));
					
					$key = md5(pack('N',$numbers1 / $spaces1) . pack('N',$numbers2 / $spaces2) . $nonce, true);
					
					$this->server->sendData($clientID, "HTTP/1.1 101 Switching Protocols\r\n");
					$this->server->sendData($clientID, "Upgrade: WebSocket\r\n");
					$this->server->sendData($clientID, "Connection: Upgrade\r\n");
					$this->server->sendData($clientID, "Sec-WebSocket-Origin: {$this->origin}\r\n");
					$this->server->sendData($clientID, "Sec-WebSocket-Location: {$this->location}\r\n");
					$this->server->sendData($clientID, "\r\n");
					$this->server->sendData($clientID, $key);
					break;
					
				case isset($headers['Sec-WebSocket-Key']):
					$this->serverTypes[$clientID] = 2;
					
					if($this->debug) echo "Received secure2 headers from {$clientID}\n";
					
					$key = base64_encode(hash('sha1', $headers['Sec-WebSocket-Key'] . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));;
					$nonce = base64_encode(mt_rand() & 0xFFFF);
					$this->nonces[$clientID] = $nonce;
					$maskingKey = hash('sha1', $this->keys[$clientID] . $this->nonces[$clientID] . '61AC5F19-FBBA-4540-B96F-6561F1AB40A8');
					$this->keys[$clientID] = $maskingKey;
					
					$this->server->sendData($clientID, "HTTP/1.1 101 Switching Protocols\r\n");
					$this->server->sendData($clientID, "Upgrade: WebSocket\r\n");
					$this->server->sendData($clientID, "Connection: Upgrade\r\n");
					$this->server->sendData($clientID, "Sec-WebSocket-Origin: {$this->origin}\r\n");
					$this->server->sendData($clientID, "Sec-WebSocket-Location: {$this->location}\r\n");
					$this->server->sendData($clientID, "Sec-WebSocket-Accept: {$acceptKey}\r\n");
					$this->server->sendData($clientID, "Sec-WebSocket-Version: 4\r\n");
					$this->server->sendData($clientID, "Sec-WebSocket-Nonce: {$nonce}\r\n");
					$this->server->sendData($clientID, "\r\n");
					break;
					
				default:
					$this->serverTypes[$clientID] = 0;
					
					if(!$this->allowBasic) $this->server->disconnectClient($clientID);
					
					if($this->debug) echo "Received basic headers from {$clientID}\n";
					
					$this->server->sendData($clientID, "HTTP/1.1 101 Web Socket Protocol Handshake\r\n");
					$this->server->sendData($clientID, "Upgrade: WebSocket\r\n");
					$this->server->sendData($clientID, "Connection: Upgrade\r\n");
					$this->server->sendData($clientID, "WebSocket-Origin: {$this->origin}\r\n");
					$this->server->sendData($clientID, "WebSocket-Location: {$this->location}\r\n");
					$this->server->sendData($clientID, "\r\n");
			}
			
			$this->processHeaders($clientID, $headers, $url, $http);
			
		} else {

			switch($this->serverTypes[$clientID]) {
				case 2:
					$maskingNonce = substr($data, 0, 4);
					$data = substr($data, 4);
					
					$frameKey = hash('sha1', $maskingNonce . $this->keys[$clientID]);
					
					$newData = '';
					foreach(str_split($data) as $i => $char) {
						$newData .= $char ^ $framekey[$i % 20];
					}
					$data = $newData;
					
					if($this->debug) echo "Received {$data} from {$clientID}\n";

					$this->processData($clientID, $data);
					
					break;
					
				default:
					$data = trim($data, "\x00");

					if($this->debug) echo "Received {$data} from {$clientID}\n";

					$this->processData($clientID, $data);
			}
			
		}
	}
	
	/**
	* Outputs a message to all connected clients
	* 
	* @param string Message to broadcast (in UTF-8 encoding)
	*/
	public function broadcast($data) {
		$this->server->broadcast($this->wrapData($data));
	}
	
	/**
	* Outputs as message to a client
	* 
	* @param string Client Identifier
	* @param string Message to send (in UTF-8 encoding)
	*/
	public function sendData($clientID, $data) {
		$this->server->sendData($clientID, $this->wrapData($data));
	}
	
	/**
	* Outputs as binary stream to a client
	* 
	* @param string Client Identifier
	* @param string Data to send
	*/
	public function sendBinary($clientID, $data) {
		$this->server->sendData($clientID, $this->wrapData($data, 5));
	}
	
	public function wrapData($data, $type=4) {
		switch($this->serverType) {
			case 0:	// Websockets October 09
				return "\x00{$data}\xFF";
				
			case 1: // Websockets August 10
				$len = 0x00 . (strlen($data) & 0xFF);
				return "\xFF{$len}{$data}";
				
			case 2: // Websockets January 11
				$len = strlen($data);
				
				if($len > 0x7F) {
					if($len > 0xFFFF) {
						$len = chr(127) . pack('N', 0x00) . pack('N', $len);
					} else {
						$len = chr(126) . pack('n', $len);
					}
				} else {
					$len = chr($len);
				}
				
				$opcode = chr($type);
				return "{$opcode}{$len}{$data}";
				
				/*
				$maskingNonce = pack('n', mt_rand(0, 65535));
				$frameKey = hash('sha1', $maskingNonce . $this->keys[$clientID]);
				
				$newData = '';
				foreach(str_split($data) as $i => $char) {
					$newData .= $char ^ $framekey[$i % 20];
				}
				
				return $maskingNonce . $newData;
				*/
		}
	}
	
	/**
	* Startup method called by the SocketServer to inject itself for later use
	*
	* @param SocketServer Server instance used to communicate with clients
	*/
	public function setServer($server) {
		$this->server = $server;
	}
	
	/**
	* Internal callback method to initialize a client connection
	*
	* @param string Client Identifier
	*/
	public function connectClient($clientID) {
		if($this->debug) echo "Client {$clientID} connected.\n";
	}
	
	/**
	* Internal callback method to terminate a client connection
	*
	* @param string Client Identifier
	*/
	public function disconnectClient($clientID) {
		if($this->debug) echo "Client {$clientID} disconnected.\n";
	}
}

interface ISocketListener {

	/** 
	* Called for each request received from a client.
	*
	* A "Request" is defined as the data up to a delimiter, as 
	* specified by $server->delimiter, or the complete data that was
	* received from a client before they disconnected.
	*
	* @param string Client Identifier
	* @param string Complete request as received from the client (without the delimiter)
	*/
	public function recvData($clientID, $data);
	
	/**
	* Called on startup to inject the server object used to respond to and manage clients
	* 
	* @param SocketServer Server instance
	*/
	public function setServer($server);
	
	/**
	* Called when a client connects to the server
	*
	* @param string Client Identifier
	*/
	public function connectClient($clientID);

	/**
	* Called when a client disconnects from the server
	*
	* @param string Client Identifier
	*/
	public function disconnectClient($clientID);
	
	/**
	* Called every iteration of the server loop
	*/
	public function poll();

}

class SocketServer {

	/** 
	* An array of 'End Of Data' markers for a complete request, this will vary depending on your protocol.
	* If your protocol does not provide an EOD or EOF token, set this to your line ending.
	**/
	protected $delimiters = array("\xFF", "\r\n\r\n");
	
	/**
	* The maximum size of a single chunk of data to read. These chunks will be combined into 
	* a complete request for processing.
	*
	* Lower sizes mean better concurrency, Higher sizes mean faster transfers.
	* The optimum value should be your network MTU - 1492 for many DSL connections, 1500 for ethernet
	**/
	public $packetSize = 1500;
	
	/**
	* The time (in microseconds) to wait for data from existing connections before checking for new 
	* connections and polling listeners for events. 
	* 
	* Higher values mean lower CPU usage, but slower response times to new clients.
	*
	* The default of 10000 (10ms) should handle most cases. This value should NOT significantly impact
	* concurrency.
	*/
	public $selectTime = 10000;

	public $connections = array();
	public $inputBuffer = array();
	public $outputBuffer = array();
	
	protected $lastPoll = array();
	protected $disconnectQueue = array();
	protected $listeners = array();
	protected $socket;
	protected $port = '8000';
	protected $bindip = '0.0.0.0';

	/**
	* Attach an object that implements ISocketListener to handle events for this server.
	*
	* @param ISocketListener Object to handle socket events
	**/
	public function addListener($listener) {
		if(!($listener instanceof ISocketListener))
			trigger_error('Listener ' . get_class($listener) . ' is not an ISocketListener', E_USER_NOTICE);

		$listener->setServer($this);
		
		$this->listeners[] = $listener;
	}

	/**
	* Append data to the output buffer to be sent to a client.
	*
	* @param string Client Identifier
	* @param string Data to send
	**/
	public function sendData($clientID, $data) {
		$this->outputBuffer[$clientID][] = $data;
	}
	
	/** 
	* Broadcast a message to all connected clients.
	*
	* @param string Data to send
	**/
	public function broadcast($data) {
		foreach($this->connections as $clientID => $socket) {
			$this->sendData($clientID, $data);
		}
	}
	
	/**
	* Disconnect a client from the server.
	* Notifies all listeners, allowing them to send any EOD signals, before dropping the connection.
	*
	* @param string Client Identifier
	**/
	public function disconnectClient($clientID) {
		if(isset($this->connections[$clientID])) {
			$this->disconnectQueue[] = $clientID;
		}
	}

	/**
	* Reads a chunk of data from the stream, adds the data to the input buffer for that client, 
	* and sends complete packets to the listeners when they're ready.
	*
	* @param string Client Identifier
	**/
	protected function readData($clientID) {
		$stream = $this->connections[$clientID];
		if(!$stream || feof($stream))
			return $this->disconnectClient($clientID);
		
		$line = fread($stream, $this->packetSize);

		if($line !== FALSE && $line !== '') {
		
			$this->inputBuffer[$clientID] .= $line;
			
			foreach($this->delimiters as $delim) {
				if(strpos($this->inputBuffer[$clientID], $delim) !== FALSE) {
					list($content, $this->inputBuffer[$clientID]) = explode($delim, $this->inputBuffer[$clientID], 2) + array('', '');
					
					foreach($this->listeners as $listener) {
						$listener->recvData($clientID, $content);
					}
					
					break;
				}
			}
		}
	}
	
	/**
	* Returns the numbers of current connections
	*/
	public function connectionCount() {
		return count($this->connections);
	}

	/**
	* Creates a SocketServer on a specific IP and Port.
	*
	* @param int Port to bind to
	* @param string IP address to bind to (use 0.0.0.0 for all)
	* @param array End of Data marker(s) for the target protocol (see: $delimiters)
	**/
	public function __construct($port = NULL, $bindip = NULL, $delimiters = NULL) {
		if($port)
			$this->port = $port;
		
		if($bindip)
			$this->bindip = $bindip;
			
		if($delimiters)
			$this->delimiters = $delimiters;
		
		$this->socket = stream_socket_server("tcp://{$this->bindip}:{$this->port}", $errno, $errstr);
		
		if(!$this->socket) {
			trigger_error('Failed to create socket: ' . $errstr, E_USER_ERROR);
		}
	}
	
	/**
	* Main execution loop
	**/
	public function run() {
	
		$start = floor(microtime(true) * 1000);
		
		while($conn = @stream_socket_accept($this->socket, 0)) {
			$clientID = hash('sha1', uniqid('', true));
			
			stream_set_blocking($conn, 0);
			$this->connections[$clientID] = $conn;
			$this->inputBuffer[$clientID] = '';
			
			foreach($this->listeners as $listener) {
				$listener->connectClient($clientID);
			}
		}
		
		if(!empty($this->connections)) {
			$read = $this->connections;
			$write = array();
			$except = array();
			
			$start = floor(microtime(true) * 1000);
			
			if(@stream_select($read, $write, $except, 0, $this->selectTime)) {
				foreach($read as $stream) {
					$clientID = array_search($stream, $this->connections);
					
					$this->readData($clientID);
				}
			}
			
			foreach($this->connections as $connection) {
				$clientID = array_search($connection, $this->connections);
				
				if(!$connection)
					$this->disconnectClient($clientID);
				
				if(!empty($this->outputBuffer[$clientID])) {
					foreach($this->outputBuffer[$clientID] as $key => $line) {
						$result = @fwrite($connection, $line);
						
						if($result === FALSE)
							$this->disconnectClient($clientID);

						unset($this->outputBuffer[$clientID][$key]);
					}
				}
			}
		} else {
			usleep($this->selectTime);
		}

		foreach($this->listeners as $index => $listener) {
			if(!isset($this->lastPoll[$index]) || $this->lastPoll[$index] + $listener->pollFrequency < $start) {
				$this->lastPoll[$index] = $start;
				$listener->poll();
			}
		}

		if($this->disconnectQueue) {
			$this->disconnectQueue = array_flip(array_unique($this->disconnectQueue));
			foreach($this->connections as $clientID => $socket) {
				if(!$socket || isset($this->disconnectQueue[$clientID])) {
					foreach($this->listeners as $listener) {
						$listener->disconnectClient($clientID);
					}
					
					@fclose($this->connections[$clientID]);
					unset($this->connections[$clientID]);
				}
			}
			$this->disconnnectQueue = array();
		}
				
		return true;
	}
	
}