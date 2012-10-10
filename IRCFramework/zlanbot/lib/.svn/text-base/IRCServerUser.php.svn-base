<?php

/**
* IRC User
*
* Represents a user in a channel that the bot
* Holds information about a specific user
*
* @author Viper-7
* @date 2009/08/27
* @package V7IRCFramework
*/
class IRCServerUser
{
	/**
	* Array of IRCServerUser objects of known users indexed by nickname
	*
	* @var Array
	*/
	public static $users;
	
	/**
	* User's nickname
	*
	* @var String
	*/
	public $nick;
	
	/**
	* User's ident
	*
	* @var String
	*/
	public $ident;
	
	/**
	* User's hostname
	*
	* @var String
	*/
	public $host;
	
	/**
	* User's mode (@ = op, % = halfop, + = voice)
	*
	* @var String
	*/
	public $mode;
	
	/**
	* Array of IRCServerChannels this user is currently occupying
	*
	* @var Array
	*/
	public $channels;
	
	/**
	* Timestamp of the last user activity
	*
	* @var Int
	*/
	public $last_activity;
	
	
	private function __construct($nick, $ident, $host, $mode, $channel='')
	{
		$this->nick = $nick;
		$this->ident = $ident;
		$this->host = $host;
		$this->mode = $mode;
		
		self::$users[$nick] = $this;
		
		if($channel)
		{
			$this->channels[$channel] = IRCServerChannel::getChannel($channel);
			$this->channels[$channel]->users[$nick] = $this;
		}
	}
	
	/**
	* Creates an IRCServerUser object from the specified hostmask
	*
	* @param String Hostmask to lookup
	* @param Boolean Should this user be cached?
	* @return IRCServerUser
	*/
	public static function getByHostmask($host, $channel='')
	{
		$details = self::decodeHostmask($host);
		if($details)
		{
			if(empty(self::$users[$details['nick']]))
			{
				$user = new IRCServerUser($details['nick'], $details['ident'], $details['host'], $details['mode'], $channel);
			} else {
				$user = self::$users[$details['nick']];
				if(empty($user->host))
				{
					$user->ident = $details['ident'];
					$user->host = $details['host'];
					$user->mode = $details['mode'];
				}
			}
			
			$user->last_activity = time();
			
			return $user;
		}
	}

	/**
	* Decodes a users hostmask into nick, ident, host & mode
	*
	* @param String User's hostmask
	* @return Array
	*/
	public static function decodeHostmask($host)
	{
		if(preg_match('/^(.+?)!(.+?)@(.+?)$/', $host, $matches))
		{
			$mode = '';
			if(substr($matches[1],0,1) == '@') $mode = '@';
			if(substr($matches[1],0,1) == '+') $mode = '+';
			if(substr($matches[1],0,1) == '%') $mode = '%';
			
			return Array('nick' => trim($matches[1], '$+%'), 'mode' => $mode, 'ident' => $matches[2], 'host' => $matches[3]);
		}
	}
	
	/**
	* Gets or creates an IRCServerUser object of the supplied name
	* 
	* @param string User's nickname
	* @param string User's ident
	* @param string User's hostname
	* @param string Channel to add the user to
	* @return IRCServerUser
	*/
	public static function getUser($nick, $channel='')
	{
		$nick = trim($nick, '@%+');
		if(empty(self::$users[$nick]))
		{
			self::$users[$nick] = new IRCServerUser($nick, '', '', '', $channel);
		}

		return self::$users[$nick];
	}
	
	public function send_msg($message)
	{
		IRCServerConnection::$server_connection->send_line("PRIVMSG {$this->nick} :{$message}");
	}
}
