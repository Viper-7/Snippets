<?php
/**
* Represents a client connected to the socket server. 
*
* Provides convenience methods to respond to the client or force a disconnection.
**/
class SocketServerClient {
	protected static $clients = array();

	protected $clientID;
	protected $server;
	protected $input_buffer;
	protected $tag
	

	/**
	* Fetches the Unique Identifier assigned to this client
	**/
	public function getID() {
		return $this->clientID;
	}
	
	/**
	* Set a user defined tag for this client, can be used to track the username, ID, etc 
	* of the remote client.
	*
	* $param string Tag value to assign
	**/
	public function setTag($tag) {
	
	}
	
	/**
	* Retrieve the user defined tag value for this client
	**/
	public function getTag() {
		return $this->tag;
	}
	
	/**
	* Retrieve the Hostname (or IP) of the client
	**/
	public function getIP() {
		return $this->server->getIP($this->clientID);
	}
	
	/**
	* Sends a message to the client
	*
	* @param string Data to send
	**/
	public function send($data) {
		$this->server->sendData($this->clientID, $data);
	}
	
	/**
	* Requests disconnection of the client (soft-disconnect)
	**/
	public function disconnect() {
		$this->server->disconnectClient($this->clientID);
	}

	
	/**
	* Handles the connection of a new client
	*
	* @param string Unique Identifier for this client
	* @param SocketServer Server instance to which this client is attached
	**/
	public function __construct($clientID, $server) {
		$this->clientID = $clientID;
		$this->server = $server;
		
		self::$clients[$clientID] = $this;
	}
	
	/**
	* Called automatically by SocketListener on incoming messages
	*
	* @param string Data received
	**/
	public function recvData($data) {
		$this->input_buffer = $data;
	}
}