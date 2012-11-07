<?php
/**
* Represents a client connected to the socket server. 
*
* Provides convenience methods to respond to the client or force a disconnection.
**/
class SocketServerClient implements ISocketServerClient {
	protected static $clients = array();

	protected $clientID;
	protected $server;
	protected $serverID;
	protected $tag;
	
	/**
	* Fetches the Unique Identifier assigned to this client
	**/
	public function getID() {
		return $this->clientID;
	}
	
	/**
	* Sets a user defined tag for this client, can be used to track the username, ID, etc 
	* of the remote client.
	*
	* $param string Tag value to assign
	**/
	public function setTag($tag) {
	
	}
	
	/**
	* Retrieves the user defined tag value for this client
	**/
	public function getTag() {
		return $this->tag;
	}
	
	/**
	* Retrieves the Hostname (or IP) of the client
	**/
	public function getIP() {
		return $this->server->getIP($this->clientID);
	}
	
	public function getServerID() {
		return $this->serverID;
	}
	
	/**
	* Sends a message to a client, via the output buffer
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
	* Broadcasts a message to all clients *EXCEPT* this one
	**/
	public function broadcast($data, $serverID = NULL) {
		$this->server->broadcast($data, $serverID, $this->clientID);
	}
	
	/**
	* Handles the connection of a new client
	*
	* @param string Unique Identifier for this client
	* @param SocketServer Server instance to which this client is attached
	* @param string Server Identifier
	**/
	public function __construct($clientID, $server, $serverID) {
		$this->clientID = $clientID;
		$this->server = $server;
		$this->serverID = $serverID;
		
		self::$clients[$clientID] = $this;
	}
	
	/**
	* Called automatically by SocketListener on incoming messages
	*
	* @param string Data received
	**/
	public function recvData($data) {
	}
}