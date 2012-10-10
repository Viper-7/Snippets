<?php
/**
* Fetches information from a Halflife 2 server with player info and stats
*
* Example Usage:
* <code>
* $serverinfo = new HL2ServerInfo('123.231.213.132', '27015');
* if($serverinfo->ping) {
* 	echo "{$serverinfo->name} is online ";
* 	echo "with {$serverinfo->numplayers} players currently playing";
* }
* </code>
* 
* @package HL2ServerInfo
*/
class HL2ServerInfo
{
	private $dom;
	private $dom2;
	
	/**
	* Hostname and Port of the server
	*
	* @var string 
	*/
	public $host;

	/**
	* Server name
	* 
	* @var string
	*/
	public $name;

	/**
	* Server mod (hl2dm, cstrike, tf2, etc)
	* 
	* @var string
	*/
	public $gametype;

	/**
	* Current map
	* 
	* @var string
	*/
	public $map;

	/**
	* Ping time for server (in ms)
	* 
	* @var int
	*/
	public $ping;

	/**
	* Number of current players
	* 
	* @var int
	*/
	public $numplayers;

	/**
	* Maximum players
	* 
	* @var int
	*/
	public $maxplayers;

	/**
	* Array of HL2Player objects containing details about the current players.
	* 
	* @var array
	*/
	public $players;

	/**
	* Server Protocol
	* 
	* @var int
	*/
	public $protocol;
	
	/**
	* Server Mod Dir
	* 
	* @var string
	*/
	public $gamedir;
	
	/**
	* Server Mod Name
	* 
	* @var string
	*/
	public $gamename;
	
	/**
	* Dedicated Server Flag
	* 
	* @var boolean
	*/
	public $dedicated;
	
	/**
	* Server OS
	* 
	* @var string
	*/
	public $sv_os;
	
	/**
	* Server is passworded Flag
	* 
	* @var boolean
	*/
	public $password;
	
	/**
	* Server is Secure Flag
	* 
	* @var boolean
	*/
	public $secure;
	
	/**
	* Server version
	* 
	* @var string
	*/
	public $version;	

	/**
	* Connects to the server and downloads information
	*
	* @param string IP Address to connect to
	* @param string Port to connect to
	*/
	public function __construct($ip, $port = 27015)
	{
		$stats = shell_exec("bin/qstat -H -P -hc -tc -htmlmode -sort F -xml -a2s $ip:$port");
		$stats2 = shell_exec("bin/qstat -H -R -htmlmode -xml -a2s $ip:$port");
		
		$this->dom = new DomDocument();
		if($this->dom->loadXML($stats))
		{
			$this->getServerInfo();
		}

		$this->dom2 = new DomDocument();
		if($this->dom2->loadXML($stats2))
		{
			$this->getRules();
		}
	}
	
	/**
	* Fetches the main server information
	*/
	private function getServerInfo()
	{
		$server = $this->dom->getElementsByTagName('server')->item(0);
		$this->host = $server->getElementsByTagName('hostname')->item(0)->nodeValue;
		$this->name = $server->getElementsByTagName('name')->item(0)->nodeValue;
		$this->gametype = $server->getElementsByTagName('gametype')->item(0)->nodeValue;
		$this->map = $server->getElementsByTagName('map')->item(0)->nodeValue;
		$this->numplayers = $server->getElementsByTagName('numplayers')->item(0)->nodeValue;
		$this->maxplayers = $server->getElementsByTagName('maxplayers')->item(0)->nodeValue;
		$this->ping = $server->getElementsByTagName('ping')->item(0)->nodeValue;
		
		$this->getPlayerInfo();
	}
	
	/**
	* Fetches the player information
	*/
	private function getPlayerInfo()
	{
		if(count($this->dom->getElementsByTagName('server')->item(0)->getElementsByTagName('players')->item(0)) == 0)
		{
			$this->players = array();
			return FALSE;
		}
		
		$players = $this->dom
						->getElementsByTagName('server')->item(0)
						->getElementsByTagName('players')->item(0)
						->getElementsByTagName('player');
		
		foreach($players as $player) {
			$playerobj = new HL2Player();
			$playerobj->name = $player->getElementsByTagName('name')->item(0)->nodeValue;
			$playerobj->score = $player->getElementsByTagName('score')->item(0)->nodeValue;
			$playerobj->time = trim($player->getElementsByTagName('time')->item(0)->nodeValue);
			
			$playerarr[] = $playerobj;
		}
		$this->players = $playerarr;
	}
	
	private function getRules()
	{
		if(count($this->dom2->getElementsByTagName('server')->item(0)->getElementsByTagName('rules')->item(0)) == 0)
		{
			return FALSE;
		}
		
		$rules = $this->dom2
						->getElementsByTagName('server')->item(0)
						->getElementsByTagName('rules')->item(0)
						->getElementsByTagName('rule');
		
		foreach($rules as $rule)
		{
			switch($rule->getAttributeNode('name')->value)
			{
				case 'protocol':
					$this->protocol = (int)$rule->nodeValue;
					break;
				case 'gamedir':
					$this->gamedir = $rule->nodeValue;
					break;
				case 'gamename':
					$this->gamename = $rule->nodeValue;
					break;
				case 'sv_os':
					$this->sv_os = $rule->nodeValue;
					break;
				case 'version':
					$this->version = $rule->nodeValue;
					break;
				case 'dedicated':
					$this->dedicated = $rule->nodeValue ? true : false;
					break;
				case 'password':
					$this->password = $rule->nodeValue ? true : false;
					break;
				case 'secure':
					$this->secure = $rule->nodeValue ? true : false;
					break;
			}
		}
	}
}


/**
* Stores information about a specific player on the server
*
* @package HL2ServerInfo
*/
class HL2Player
{
	/**
	* Players Name
	*
	* @var string
	*/
	public $name;

	/**
	* Current score
	*
	* @var int
	*/
	public $score;

	/**
	* Time on server (formatted)
	*
	* @var string
	*/
	public $time;
}