<?php
class NextLineListener extends SocketClientListener {
	public function processInput($newData) {
		$callback = $this->callback;
		$callback($newData[0]);
		$this->client->detach($this);
		return $newData[0];
	}
}

class TokenListener extends SocketClientListener {
	protected $token;
	
	public function __construct($token, $callback) {
		$this->token = $token;
		parent::__construct($callback);
	}
	
	public function processInput($newData) {
		foreach($newData as $key => $line) {
			if(strpos($line, $this->token) !== FALSE) {
				$callback = $this->callback;
				$callback($line);
				return $line;
			}
		}
	}
}

class RegexListener extends SocketClientListener {
	protected $pattern;
	
	public function __construct($pattern, $callback) {
		$this->pattern = $pattern;
		parent::__construct($callback);
	}
	
	public function processInput($newData) {
		foreach($newData as $key => $line) {
			if(preg_match($this->pattern, $line, $matches)) {
				$callback = $this->callback;
				$callback($matches);
				return $line;
			}
		}
	}
}

abstract class SocketClientListener {
	public $client;
	public $pollFrequency = 0;
	
	protected $callback
	
	public function __construct($callback) {
		$this->callback = $callback;
	}
	
	public function processInput($newData) {
		
	}
	
	public function onAttach() {}
	public function onDetach() {}
	public function poll() {}
}

class TCPSocketClient extends SocketClient {
	public function connect() {
		$this->fp = stream_socket_client("tcp://{$this->host}:{$this->port}");
	}
}

abstract class SocketClient {
	protected $host;
	protected $port;
	protected $bind_ip;
	protected $fp;
	protected $lastPoll = array();
	protected $inputBuffer = array();
	protected $outputBuffer;
	protected $listeners = array();
	protected $select_time = 1000;
	
	public function __construct($host, $port) {
		$this->host = $host;
		$this->port = $port;
		
		$this->outputBuffer = fopen('php://temp', 'w');
	}

	public function connect() {
		trigger_error(get_class($this) . ' must implement connect()', E_USER_FATAL);
	}
	
	public function send($data) {
		if($this->fp === null)
			trigger_error('Cannot send data to closed socket');
		
		fwrite($this->fp, $data);
	}
	
	public function poll() {
		$read = array($this->fp);
		$write = array($this->outputBuffer);
		$except = array();
		if(stream_select($read, $write, $except, 0, $this->select_time)) {
			foreach($read as $fp) {
				$this->inputBuffer[] = fgets($fp);
				$this->processInput();
			}
			
			foreach($write as $fp) {
				rewind($fp);
				stream_copy_to_stream($fp, $this->fp);
				ftruncate($fp);
			}
		}

		$start = floor(microtime(true) * 1000);
		
		/**
		* Poll listeners to allow them to run any global tasks
		**/
		foreach($this->listeners as $index => $listener) {
			if($listener->pollFrequency && !isset($this->lastPoll[$index]) || $this->lastPoll[$index] + $listener->pollFrequency < $start) {
				$this->lastPoll[$index] = $start;
				$listener->poll();
			}
		}
	}
	
	public function attach($listener) {
		$listener->client = $this;
		$this->listeners[] = $listener;
		$listener->onAttach();
	}
	
	public function detach($listener) {
		$key = array_search($listener, $this->listeners);
		$listener->onDetach();
		unset($this->listeners[$key]);
	}
	
	protected function processInput() {
		foreach($this->listeners as $handler) {
			$return = $handler->processInput($this->inputBuffer);
			
			if($return) {
				if(!is_array($return)) $return = array($return);
				
				$this->inputBuffer = array_diff($this->inputBuffer, $return);
			}
		}
	}
	
	public function __destruct() {
		fclose($this->fp);
	}
}