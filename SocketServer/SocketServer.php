<?php
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
	
	protected $clientIPs = array();
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
	* Fetches the remote IP (or address) of the connected client
	**/
	public function getIP($clientID) {
		return $this->clientIPs[$clientID];
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
		
		/**
		* Accept new connections, generating a unique Client ID for each
		**/
		while($conn = @stream_socket_accept($this->socket, 0, $clientIP)) {
			$clientID = hash('sha1', uniqid('', true));
			
			stream_set_blocking($conn, 0);
			$this->connections[$clientID] = $conn;
			$this->clientIPs[$clientID] = $clientIP;
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
			
			/**
			* Process incoming data from connected clients
			**/
			if(@stream_select($read, $write, $except, 0, $this->selectTime)) {
				foreach($read as $stream) {
					$clientID = array_search($stream, $this->connections);
					
					$this->readData($clientID);
				}
			}
			
			/**
			* Process output buffers and send data to clients.
			**/
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

		/**
		* Poll listeners to allow them to run any global tasks
		**/
		foreach($this->listeners as $index => $listener) {
			if(!isset($this->lastPoll[$index]) || $this->lastPoll[$index] + $listener->pollFrequency < $start) {
				$this->lastPoll[$index] = $start;
				$listener->poll();
			}
		}

		/**
		* If we've detected a client's disconnection, remove them from the connection pool and notify 
		* the listeners.
		**/
		if($this->disconnectQueue) {
			$this->disconnectQueue = array_flip(array_unique($this->disconnectQueue));
			foreach($this->connections as $clientID => $socket) {
				if(!$socket || isset($this->disconnectQueue[$clientID])) {
					foreach($this->listeners as $listener) {
						$listener->disconnectClient($clientID);
					}
					
					@fclose($this->connections[$clientID]);
					unset($this->connections[$clientID]);
					unset($this->clientIPs[$clientID]);
				}
			}
			$this->disconnnectQueue = array();
		}
				
		return true;
	}
	
}