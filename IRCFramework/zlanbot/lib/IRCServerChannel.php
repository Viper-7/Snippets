<?php

/**
* IRC Channel
*
* Represents a channel that the bot is currently inside.
* Holds information about the channel itself and the users inside
*
* @author Viper-7
* @date 2009/08/27
* @package V7IRCFramework
*/
class IRCServerChannel
{
	/**
	* Array of open IRCServerChannel objects indexed by channel name
	*
	* @var Array
	*/
	public static $channels;
	
	/**
	* This channel's name
	*
	* @var String
	*/
	public $channel;
	
	/**
	* Array of IRCServerUsers occupying this channel
	*
	* @var Array
	*/
	public $users;
	
	/**
	* The current channel modes
	* 
	* $var String
	*/
	public $modes;
	
	/**
	* The current channel topic
	*
	* @var String
	*/
	public $topic;
	
	/**
	* Flag to tell if we're currently in the channel
	*
	* @var Boolean
	*/
	public $online;
	
	/**
	* The bots current nickname
	*/
	public $nick;
	
	/**
	* System variable to hold the partial contents of a NAMES response
	*/
	protected $names = Array();
	
	/**
	* String templating engine
	*/
	protected $template;
	
	/**
	* Reference to the current server object
	*
	* @var IRCServerConnection
	*/
	public $server;
	
	protected function __construct($channel)
	{
		$this->channel = $channel;
		$this->server = IRCServerConnection::$server_connection;
		$this->template = new StringTemplate();
		$this->nick = $this->server->nick;
	}
	
	/**
	* Called for every cycle of the bot's processing loop.
	* Once per second, and once per line of text received
	*
	* $param Int Number of cycles since startup
	*/
	public function poll($cycle)
	{
	
	}
	
	/**
	* Gets or creates an IRCServerChannel object of the supplied channel name
	*
	* @param string Channel name (Including the #)
	* @return IRCServerChannel
	*/
	public static function getChannel($channel)
	{
		if(empty(self::$channels[strtolower($channel)]))
		{
			$channel_obj = trim($channel, '#');
			self::$channels[strtolower($channel)] = new $channel_obj(strtolower($channel));
		}
		
		return self::$channels[strtolower($channel)];
	}
	
	/**
	* Send a message in the channel
	*
	* @param String Message to send
	*/
	public function send_msg($message)
	{
		$this->server->send_msg($this->channel, $message);
	}
	
	/**
	* Kick a person from the channel
	*
	* @param String Nickname of the user to kick
	* @param String Reason to give
	*/
	public function kick_user($nick, $message)
	{
		$this->server->send_line("KICK {$this->channel} {$nick} :{$message}");
	}
	
	/**
	* Adds or Removes modes that have been set on the channel
	*
	* @access private
	*/
	public function add_modes($newmodes)
	{
		// not yet implemented
	}
	
	/**
	* Event raised when joining the channel
	*/
	public function event_joined()
	{
	
	}
	
	/**
	* Event raised when the bot is kicked
	*
	* @param IRCServerUser Person who kicked the bot
	* @param String The reason they gave
	*/
	public function event_kicked($who, $why)
	{

	}
	
	/**
	* Event raised when the channel modes are changed
	*
	* @param IRCServerUser Person who changed the mode
	* @param String The new channel modes
	*/
	public function event_mode($who, $modes)
	{

	}
	
	/**
	* Event raised when someone sends a notice to the channel
	*
	* @param IRCServerUser Person who send the notice
	* @param String The notice message
	*/
	public function event_notice($who, $message)
	{
	
	}
	
	/**
	* Event raised when someone speaks in the channel
	*
	* @param IRCServerUser Person who spoke
	* @param String What they said
	*/
	public function event_msg($who, $message)
	{
	
	}
	
	/**
	* Event raised when someone joins
	*
	* @param IRCServerUser Person who joined
	*/
	public function event_join($who)
	{
	
	}
	
	/**
	* Event raised when someone leaves
	*
	* @param IRCServerUser Person who left
	*/
	public function event_part($who)
	{
	
	}

	/**
	* Event raised when someone quits
	*
	* @param IRCServerUser Person who left
	* @param String Quit message
	*/
	public function event_quit($who, $message)
	{
	
	}
	
	/**
	* Event raised when someone gets kicked
	*
	* @param IRCServerUser Person who did the kick
	* @param String Reason they gave
	* @param String Person who got kicked
	*/
	public function event_kick($who, $message, $victim)
	{
	
	}
	
	/**
	* System Event raised when joining a channel
	* Used to populate the user list for the channel
	*
	* @access private
	*/
	public function event_names($users)
	{
		$this->names = array_merge($this->names, explode(' ', $users));
	}
	
	/**
	* System Event raised when joining a channel
	* Processes the user list into user objects
	*
	* @access private
	*/
	public function event_names_end()
	{
		$this->online = TRUE;
		foreach($this->names as $nick)
		{
			$this->users[$nick] = IRCServerUser::getUser($nick, $this->channel);
		}
		$this->names = Array();
		$this->event_joined();
	}
}