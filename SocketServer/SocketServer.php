<?php
include_once 'ISocketListener.php';
include_once 'ISocketServerClient.php';

include_once 'SocketListener.php';
include_once 'SocketServerClient.php';

class SocketServer {
	protected $delimiters;
	protected $bindip = '0.0.0.0';
	protected $selectTime = 10000;

	protected $servers = array();
	protected $connections = array();
	protected $inputBuffer = array();
	protected $outputBuffer = array();
	
	protected $clientIPs = array();
	protected $serverIDs = array();
	protected $lastPoll = array();
	protected $disconnectQueue = array();
	protected $listeners = array();
	
	/**
	* Attach an object that implements ISocketListener to handle events for this server.
	*
	* @param ISocketListener Object to handle socket events
	**/
	public function addListener($listener, $serverID = '*') {
		if(!($listener instanceof ISocketListener))
			trigger_error('Listener ' . get_class($listener) . ' is not an ISocketListener', E_USER_NOTICE);

		$listener->setServer($this);
		
		$this->listeners[$serverID][] = $listener;
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
	public function broadcast($data, $serverID = null, $exceptClientID = null) {
		if(!is_array($exceptClientID))
			$exceptClientID = array($exceptClientID);
		
		if($serverID) {
			foreach($this->serverIDs as $clientID => $clientSID) {
				if($clientSID == $serverID) {
					if(!in_array($clientID, $exceptClientID))
						$this->sendData($clientID, $data);
				}
			}
		} else { 
			foreach($this->connections as $clientID => $socket) {
				if(!in_array($clientID, $exceptClientID))
					$this->sendData($clientID, $data);
			}
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
		if(isset($this->connections[$clientID])) {
			$stream = $this->connections[$clientID];
		}
		
		if(empty($stream) || feof($stream))
			return $this->disconnectClient($clientID);
		
		$line = fread($stream, 4096);

		if($line !== FALSE) {
			if($this->delimiters) { 
				$this->inputBuffer[$clientID] .= $line;
				
				foreach($this->delimiters as $delim) {
					if(strpos($this->inputBuffer[$clientID], $delim) !== FALSE) {
						list($content, $this->inputBuffer[$clientID]) = explode($delim, $this->inputBuffer[$clientID], 2) + array('', '');
						
						break;
					}
				}
			} else {
				$content = $line;
			}
			
			$serverID = $this->serverIDs[$clientID];
			
			if(isset($this->listeners[$serverID])) {
				foreach($this->listeners[$serverID] as $listener) {
					$listener->recvData($clientID, $content, $serverID);
				}
			}
			
			if(isset($this->listeners['*'])) {
				foreach($this->listeners['*'] as $listener) {
					$listener->recvData($clientID, $content, $serverID);
				}
			}

		}
	}
	
	/**
	* Returns the numbers of current connections
	**/
	public function connectionCount() {
		return array_sum(array_map('count', $this->connections));
	}
	
	public function setDelimiters($delimiters) {
		if(!is_array($delimiters))
			$delimiters = array($delimiters);
			
		$this->delimiters = $delimiters;
	}

	/**
	* Creates a SocketServer on a specific IP and Port.
	*
	* @param int Port to bind to
	* @param string IP address to bind to (use NULL for all)
	**/
	public function open($port = NULL, $bindip = NULL) {
		if(!$bindip)
			$bindip = $this->bindip;
			
		$socket = stream_socket_server("tcp://{$bindip}:{$port}", $errno, $errstr);
		
		if(!$socket) {
			trigger_error('Failed to create socket: ' . $errstr, E_USER_ERROR);
		}
		
		$serverID = "{$bindip}:{$port}";
		
		$this->servers[$serverID] = $socket;
		
		return $serverID;
	}
	
	/**
	* Main execution loop
	**/
	public function run() {
	
		$start = floor(microtime(true) * 1000);
		
		$clients = $this->connections ?: array();
		
		$read = array_merge($this->servers, $clients);

		$write = array();
		$except = array();
		
		/**
		* Process incoming data from connected clients
		**/
		if(stream_select($read, $write, $except, 0, $this->selectTime)) {
			foreach($read as $socket) {
				$serverID = array_search($socket, $this->servers);
				if($serverID !== FALSE) {
					$stream = stream_socket_accept($socket, 0, $clientIP);

					$clientID = hash('sha1', uniqid('', true));
					
					$this->connections[$clientID] = $stream;
					$this->serverIDs[$clientID] = $serverID;
					$this->clientIPs[$clientID] = $clientIP;
					$this->inputBuffer[$clientID] = '';
					

					if(isset($this->listeners[$serverID])) {
						foreach($this->listeners[$serverID] as $listener) {
							$listener->connectClient($clientID, $serverID);
						}
					}
					
					if(isset($this->listeners['*'])) {
						foreach($this->listeners['*'] as $listener) {
							$listener->connectClient($clientID, $serverID);
						}
					}
				}
			
				$clientID = array_search($socket, $this->connections);
				if($clientID !== FALSE) {
					$this->readData($clientID);
				}
			}
			
			/**
			* Process output buffers and send data to clients.
			**/
			foreach($this->connections as $clientID => $connection) {
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

		/**
		* Poll listeners to allow them to run any global tasks
		**/
		foreach($this->listeners as $x => $pool) {
			foreach($pool as $y => $listener) {
				$index = "{$x}:{$y}";
				if(!isset($this->lastPoll[$index]) || $this->lastPoll[$index] + $listener->pollFrequency < $start) {
					$this->lastPoll[$index] = $start;
					$listener->poll();
				}
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
				
					$serverID = $this->serverIDs[$clientID];

					if(isset($this->listeners[$serverID])) {
						foreach($this->listeners[$serverID] as $listener) {
							$listener->disconnectClient($clientID);
						}
					}
					
					if(isset($this->listeners['*'])) {
						foreach($this->listeners['*'] as $listener) {
							$listener->disconnectClient($clientID);
						}
					}
				
					@fclose($this->connections[$clientID]);
					unset($this->serverIDs[$clientID]);
					unset($this->connections[$clientID]);
					unset($this->clientIPs[$clientID]);
				}
			}
			$this->disconnnectQueue = array();
		}
				
		return true;
	}
	
}