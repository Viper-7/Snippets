<?php
/**
* IRC Server Connection Class
*
* @author Viper-7
* @date 2009/08/27
* @package V7IRCFramework
* @example channels/Simplechannel.php
* 
* Handles the connection and communication to an IRC server
*/
class IRCServerConnection
{
	/**
	* Static reference to the connection object
	*/
	public static $server_connection;
	
	/**
	* The active connection handle to the server
	*
	* @var Resource
	*/
	private $conn;
	
	/**
	* The output buffer for outbound data
	* FIFO Stack held in memory
	*
	* @var Array
	*/
	private $buffer;

	/**
	* The currently connected server
	*
	* @var String
	*/
	public $server;
	
	/**
	* The currently connected server's port
	*
	* @var Int
	*/
	public $port;
	
	/**
	* The bot's current Nickname
	*
	* @var String
	*/
	public $nick;
	
	/**
	* Flag to allow connection and polling
	*
	* @var Boolean
	*/
	public $connected;
	
	/**
	* Flag to send identification information on connection
	*
	* @var Boolean
	*/
	public $authenticated;
	
	/**
	* Flag to tell if we're 100% online and ready to roll
	*
	* @var Boolean
	*/
	public $online;

	/**
	* Object to pass messages to
	*
	* @var Object
	*/
	public $messageHandler;
	
	/**
	* Time to wait between polls
	*
	* @var Int
	*/
	public $pollInterval = 1;
	
	/**
	* Flag to control dumping of debugging information to the console
	*
	* @var Boolean
	*/
	public $debug;
	
	/**
	* Cycles since startup
	*
	* @var Int
	*/
	public $cycles;
	
	/**
	* Tracks the last time the bot was pinged, to reconnect if everything else fails
	*/
	private $lastping = PHP_INT_MAX;
	
	/**
	* Creates a new outbound buffer resource and connects to the server
	* 
	* @param String Server to connect to
	* @param String Port to connect to
	* @param String Nickname to use
	* @param Array List of channels to join
	*/
	public function __construct($server, $port, $nickname, $channels, $debug = FALSE)
	{
		if(!empty(self::$server_connection) && self::$server_connection->connected)
		{
			$this->online = FALSE;
			$this->connected = FALSE;
			throw new IRCServerException('You cannot connect to more than one server at a time!');
		}
		
		set_time_limit(0);
		register_shutdown_function(Array('IRCServerConnection', 'destroy'));
		
		$this->buffer = Array();
		$this->server = $server;
		$this->port = $port;
		$this->nick = $nickname;
		$this->debug = $debug;
		
		self::$server_connection = $this;
		
		try {
			$this->connect($channels);
		} catch(IRCServerException $e) {
			$this->online = FALSE;
			$this->connected = FALSE;
			throw $e;
		}
	}
	
	/**
	* (re)Connects to the server
	*/
	private function connect($channels)
	{
		if($this->connected)
		{
			$this->online = FALSE;
			$this->connected = FALSE;
			throw new IRCServerException('Already connected to a server!');
		}
		
		$this->conn = fsockopen($this->server,$this->port,$errno,$errstr,30);
		if(empty($this->conn))
		{
			$this->online = FALSE;
			$this->connected = FALSE;
			throw new IRCServerException('Failed to connect to server');
		}
		
		$this->connected = TRUE;
		
		foreach($channels as $channel)
		{
			IRCServerChannel::getChannel($channel);
		}
		
		$this->serverLoop();
	}
	
	public static function destroy()
	{
		self::$server_connection->connected = FALSE;
		self::$server_connection->online = FALSE;
		self::$server_connection->sendImmediate('QUIT');
	}
	
	/**
	* Process an incoming message
	*
	* @param string Raw line from the server
	*/
	private function processIncoming($message)
	{
		$parts = explode(' ', trim($message,": \r\n")) + array('', '');
		
		switch($parts[0])
		{
			case 'NOTICE':
				if(!$this->authenticated && $parts[1] == 'AUTH')
				{
					$this->send_line("NICK {$this->nick}");
					$this->send_line("USER {$this->nick} localhost {$this->server} :{$this->nick}");
					$this->authenticated = TRUE;
				}

				break;
			case 'PING':
				$this->lastping = time();
				$this->send_line('PONG ' . $parts[1]);
		}
		
		switch($parts[1])
		{
			case 'PRIVMSG':
				$content = trim(implode(' ', array_slice($parts, 3)), ": ");
				if(substr($content,0,1) == "\001")
				{
					$ctcp = true;
					$content = trim($content, "\001");
				}
				
				if($content == 'VERSION' && $ctcp)
				{
					$this->sendCTCP(IRCServerUser::getByHostmask($parts[0])->nick, 'VERSION V7IRC');
				} else {
					if($parts[2] != $this->nick)
					{
						// Process channel text
						$user = IRCServerUser::getByHostmask($parts[0]);
						$chan = IRCServerChannel::getChannel($parts[2]);
						
						$chan->event_msg($user, $content);
					} else {
						// Process private message
					}
				}
				break;
			case 'KICK':
				$content = trim(implode(' ', array_slice($parts, 4)), ": ");
				if($parts[3] == $this->nick)
				{
					$user = IRCServerUser::getByHostmask($parts[0]);
					$chan = IRCServerChannel::getChannel($parts[2]);
					$chan->online = FALSE;
					$chan->event_kicked($user, $content);
				} else {
					$user = IRCServerUser::getByHostmask($parts[0]);
					$victim = IRCServerUser::getUser($parts[3]);
					$chan = IRCServerChannel::getChannel($parts[2]);
					unset($victim->channels[$chan->channel]);
					$chan->event_kick($user, $content, $victim);
				}
				break;
			case 'JOIN':
				$chan = IRCServerChannel::getChannel($parts[2]);
				$user = IRCServerUser::getByHostmask($parts[0], $chan->channel);
				$chan->event_join($user);
				break;
			case 'PART':
				$user = IRCServerUser::getByHostmask($parts[0]);
				$chan = IRCServerChannel::getChannel($parts[2]);
				unset($user->channels[$chan->channel]);
				$chan->event_part($user);
				break;
			case 'QUIT':
				$content = trim(implode(' ', array_slice($parts, 3)), ": ");
				$user = IRCServerUser::getByHostmask($parts[0]);
				foreach(IRCServerChannel::$channels as $chan)
				{
					foreach($chan->users as $chanuser)
					{
						if($chanuser == $user)
						{
							unset($user->channels[$chan->channel]);
							$chan->event_quit($user, $content);
						}
					}
				}
				break;
			case 'NOTICE':
				$content = trim(implode(' ', array_slice($parts, 3)), ": \001");
				$user = IRCServerUser::getByHostmask($parts[0]);
				if($parts[2] != $this->nick)
				{
					$chan = IRCServerChannel::getChannel($parts[2]);
					$chan->event_notice($user, $content);
				}
				break;
			case 'MODE':
				$content = trim(implode(' ', array_slice($parts, 3)), ": \001");
				$user = IRCServerUser::getByHostmask($parts[0]);
				if($parts[2] != $this->nick)
				{
					$chan = IRCServerChannel::getChannel($parts[2]);
					$chan->event_mode($user, $content);
					$chan->add_modes($content);
				}
				break;
				
			case '005':
				$this->online = TRUE;
				break;
			case '332':
				$content = trim(implode(' ', array_slice($parts, 4)), ": ");
				$chan = IRCServerChannel::getChannel($parts[3]);
				$chan->topic = $content;
				break;
			case '353':
				$channel_obj = IRCServerChannel::getChannel(strtolower($parts[4]));
				$channel_obj->event_names(trim(implode(' ', array_slice($parts, 5)), ": "));
				break;
				
			case '366':
				$channel_obj = IRCServerChannel::getChannel(strtolower($parts[3]));
				$channel_obj->event_names_end();
				break;
		}
	}
	
	/**
	* Primary server loop
	*/
	private function serverLoop()
	{
		while($this->connected)
		{
			$this->cycles++;
			
			// If we havent been pinged in the last 5 minutes, exit
			if($this->lastping < time() - 300)
			{
				$this->connected = FALSE;
			}
			
			// If our connection has dropped, exit the loop
			if(!is_resource($this->conn))
			{
				$this->connected = FALSE;
			}
			
			if(!$this->connected)
			{
				return;
			}
			
			if($this->online)
			{
				foreach(IRCServerChannel::$channels as $channel)
				{
					if(!$channel->online && time() % 2 == 0)
					{
						$channel->autojoin();
					}
					
					if(method_exists($channel, 'poll'))
					{
						$channel->poll($this->cycles);
					}
				}
			}

			if(count($this->buffer))
			{
				// Process outbound data
				
				$message = array_shift($this->buffer);
				fputs($this->conn, $message);

				if($this->debug)
				{
					echo '  >>> ' . $message;
				}
			}

			$connections = Array($this->conn);
			$write = NULL;
			$expect = NULL;
			
			if(stream_select($connections, $write, $expect, 1, 0))
			{
				// There is data to process
				
				if(!empty($connections))
				{
					// Process inbound data
					
					foreach($connections as $connection)
					{
						$message = fgets($connection);
					}
					
					if($message == '')
					{
						// If our connection has dropped, exit the loop
						$this->connected = FALSE;
						return;
					}
					
					if($this->debug)
					{
						echo '<<<   ' . str_replace("\001", '**', $message);
					}
					
					$this->processIncoming($message);
				}
			}
		}
	}
	
	/**
	* Send a line to the server without using the message buffer
	*
	* @param String The line of text to send to the server
	*/
	public function sendImmediate($message)
	{
		if(!$this->connected)
		{
			$this->online = FALSE;
			$this->connected = FALSE;
			throw new IRCServerException('Not connected! Unable to send a message');
		}

		fputs($this->conn, trim($message) . "\n");
	}

	/**
	* Send a line to the server
	*
	* @param String The line of text to send to the server
	*/
	public function send_line($message)
	{
		if(!$this->connected)
		{
			$this->online = FALSE;
			$this->connected = FALSE;
			throw new IRCServerException('Not connected! Unable to send a message');
		}

		$this->buffer[] = trim($message) . "\n";
	}
	
	/**
	* Send a message to a channel or user
	*
	* @param String Destination user or channel for the message
	* @param String The message itself
	*/
	public function send_msg($dest, $message)
	{
		$this->send_line("PRIVMSG $dest :$message");
	}
	
	/**
	* Send a CTCP message to a channel or user
	*
	* @param String Destination user or channel for the CTCP message
	* @param String The message itself
	*/
	public function sendCTCP($dest, $message)
	{
		$this->send_line("PRIVMSG $dest :\001$message\001");
	}
}