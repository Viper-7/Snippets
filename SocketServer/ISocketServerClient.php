<?php
interface ISocketServerClient {
	/** 
	* Called for each request received from a client.
	*
	* A "Request" is defined as the data up to a delimiter, as 
	* specified by $server->delimiter, or the complete data that was
	* received from a client before they disconnected.
	*
	* @param string Complete request as received from the client (without the delimiter)
	**/
	public function recvData($data);

	/**
	* Sends a message to a client, via the output buffer
	*
	* @param string Data to send
	**/
	public function send($data);
	
	/**
	* Requests disconnection of the client (soft-disconnect)
	**/
	public function disconnect();
}
