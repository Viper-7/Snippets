<?php
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