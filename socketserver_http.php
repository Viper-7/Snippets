<?php
/** START EXAMPLE CODE **/

	// Create a basic listener to test the server
	class TestListener implements ISocketListener {
		protected $server;
		
		public function recvData($clientID, $data, $alive) {
		
			// Display debug information on the server console
			echo "Received data from {$clientID}\n";
			
			// Respond to the client immediately. For this example i'm sending a HTTP response, 
			// this is only to make testing / benchmarking easier, this library is not designed 
			// to function as a HTTP webserver!
			$content = "<html><body>Hello, {$clientID}! There are {$this->server->connectionCount()} current users.</body></html>";
			$length = strlen($content);
			$packet = "HTTP/1.0 200 OK\r\nContent-Type: text/html\r\nContent-Length: {$length}\r\nConnection: close\r\n\r\n$content\r\n";
			
			// Send the data to the client (via the output buffer)
			$this->server->sendData($clientID, $packet);
			
			// Add the client to the disconnection queue.
			//
			// The server will handle client disconnections automatically, but you can also 
			// close the connection like this once you have completed your transaction.
			$this->server->disconnectClient($clientID);
		}
		
		public function setServer($server) {
			$this->server = $server;
		}
		
		public function poll() {}

		public function connectClient($clientID) {
			echo "Client {$clientID} connected.\n";
		}
		
		public function disconnectClient($clientID, $alive) {
			echo "Client {$clientID} disconnected.\n";
		}
	}

	// Start a server on port 8000
	$server = new SocketServer(8000);

	// Instantiate our listener and attach it to the server
	$listener = new TestListener();
	$server->addListener($listener);

	// Run the server until killed
	while($server->run()) {}

/** END EXAMPLE CODE **/



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
	* @param boolean True if the client is still connected
	*/
	public function recvData($clientID, $data, $alive);
	
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
	* @param boolean True if the client is still connected. This occurs when the disconnection was
	* 				 triggered by a listener instead of the client.
	*/
	public function disconnectClient($clientID, $alive);
	
	/**
	* Called every iteration of the server loop
	*/
	public function poll();

}

class SocketServer {

	/** 
	* The 'End Of Data' marker for a complete request, this will vary depending on your protocol.
	* If your protocol does not provide an EOD or EOF token, set this to your line ending and call
	* $server->disconnectClient($clientID) from your recvData method when you are finished reading.
	**/
	public $delimiter = "\r\n\r\n";
	
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
	* Higher values mean lower CPU usage, but slower response times. The default of 1000 (1ms) should 
	* handle most cases. This value should NOT significantly impact concurrency.
	*/
	public $selectTime = 1000;
	
	protected $connections = array();
	protected $inputBuffer = array();
	protected $outputBuffer = array();
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
			foreach($this->listeners as $listener) {
				$listener->disconnectClient($clientID, @feof($this->connections[$clientID]));
			}
			
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
		if(!$stream)
			return $this->disconnectClient($clientID);
			
		$line = stream_get_line($stream, $this->packetSize, $this->delimiter);
		
		if($line === FALSE || $line === '') {
			return $this->disconnectClient($clientID);
		}
		
		$this->inputBuffer[$clientID] .= $stream;
		
		if(strpos($this->inputBuffer[$clientID], $this->delimiter) !== FALSE) {
			list($content, $this->inputBuffer[$clientID]) = explode($this->delimiter, $this->inputBuffer[$clientID], 2) + array('', '');
		}
		
		foreach($this->listeners as $listener) {
			$listener->recvData($clientID, $line, !@feof($stream));
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
	**/
	public function __construct($port = NULL, $bindip = NULL) {
		if($port)
			$this->port = $port;
		
		if($bindip)
			$this->bindip = $bindip;
		
		$this->socket = stream_socket_server("tcp://{$this->bindip}:{$this->port}", $errno, $errstr);
		
		if(!$this->socket) {
			trigger_error('Failed to create socket: ' . $errstr, E_USER_ERROR);
		}
	}
	
	/**
	* Main execution loop
	**/
	public function run() {
	
		$start = floor(microtime(true) * 1000000);
		
		while($conn = @stream_socket_accept($this->socket, 0)) {
			$clientID = hash('sha1', uniqid('', true));
			
			stream_set_blocking($conn, 0);
			$this->connections[$clientID] = $conn;
			
			foreach($this->listeners as $listener) {
				$listener->connectClient($clientID);
			}
		}
		
		if(!empty($this->connections)) {
			$read = $this->connections;
			$write = $this->connections;
			$except = array();
			
			$start = floor(microtime(true) * 1000000);
			
			do {

				if(@stream_select($read, $write, $except, 0, $this->selectTime)) {
					foreach($read as $stream) {
						$clientID = array_search($stream, $this->connections);
						
						$this->readData($clientID);
					}
					
					foreach($write as $stream) {
						$clientID = array_search($stream, $this->connections);
						
						$connection = $this->connections[$clientID];
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
				}
				
				$end = floor(microtime(true) * 1000000);

			} while(!empty($this->connections) && $end - $start < $this->selectTime);
		
			foreach($this->listeners as $listener) {
				$listener->poll();
			}
			
			$this->disconnectQueue = array_unique($this->disconnectQueue);
			foreach($this->disconnectQueue as $key => $clientID) {
				@fclose($this->connections[$clientID]);
				unset($this->connections[$clientID]);
				unset($this->disconnectQueue[$key]);
			}
		}
		
		$end = floor(microtime(true) * 1000000);
		$diff = $this->selectTime - ($end - $start);
		if($diff > 0) {
			usleep($diff);
		}
		
		return true;
	}
	
}
