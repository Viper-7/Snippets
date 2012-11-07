<?php
abstract class SocketListener implements ISocketListener {
	protected $server;
	protected $clients;
	protected $clientClass = 'SocketServerClient';

	/** 
	* Called for each request received from a client.
	*
	* A "Request" is defined as the data up to a delimiter, as 
	* specified by $server->delimiter, or the complete data that was
	* received from a client before they disconnected.
	*
	* This method should handle any packet framing or validation logic
	*
	* @param string Client Identifier
	* @param string Complete request as received from the client (without the delimiter)
	**/
	public function recvData($clientID, $data, $serverID = NULL) {
		$client = $this->clients[$clientID];
		
		$client->recvData($data);
		$this->processData($client, $data);
	}
	
	/**
	* Called for each 'packet' as decoded by the listener
	*
	* @param SocketServerClient Sender client model
	* @param string Request as received from the client (without framing)
	**/
	public function processData($client, $data) {
		
	}

	/**
	* Outputs a message to all connected clients
	* 
	* @param string Message to broadcast
	**/
	public function broadcast($data) {
		$this->server->broadcast($data);
	}
	
	/**
	* Outputs a message to a specific client
	* 
	* @param string Client Identifier
	* @param string Message to send (in UTF-8 encoding)
	**/
	public function sendData($clientID, $data) {
		$this->server->sendData($clientID, $data);
	}

	/**
	* Called frequently from the server to allow listeners to process any background tasks
	**/
	public function Poll() {
	
	}


	/**
	* Called on startup to inject the server object used to respond to and manage clients
	* 
	* @param SocketServer Server instance
	**/
	public function setServer($server) {
		$this->server = $server;
	}
	
	/**
	* Called when a client connects to the server
	*
	* @param string Client Identifier
	**/
	public function connectClient($clientID, $serverID) {
		$class = $this->clientClass;
		$this->clients[$clientID] = new $class($clientID, $this->server, $serverID);;
	}
	
	/**
	* Called when a client disconnects from the server
	*
	* @param string Client Identifier
	**/
	public function disconnectClient($clientID) {
		if(isset($this->clients[$clientID]))
			unset($this->clients[$clientID]);
	}
}