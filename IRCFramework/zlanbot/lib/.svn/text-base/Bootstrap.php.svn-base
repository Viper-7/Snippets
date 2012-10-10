<?php
/**
* Startup Script
*
* @author Viper-7
* @date 2009/08/27
* @package V7IRCFramework
*/

/**
* Simple autoloader to load bot elements and channel classes
*/
function __autoload($class_name)
{
	$class_name = basename($class_name);
	
	$files = glob('{./,lib/,ext/,channels/}' . $class_name . '.php', GLOB_BRACE);
	
	if($files)
	{
		include reset($files);
	}
	else
	{
		trigger_error('Could not find a class of the name ' . $class_name, E_USER_ERROR);
	}
}

/**
* Starts the bot, connects to $server:$port with the nickname $nick, and joins channels listed in $channels
*
* @param String Server name to connect to
* @param String Port to connect to
* @param String Nickname to use
* @param Array List of channels to join (including the #)
* @param Boolean Enable debugging output
*/
function startBot($server, $port, $nick, $channels, $debug=FALSE)
{
	while(TRUE)
	{
		try {
			$irc = new IRCServerConnection($server, $port, $nick, $channels, $debug);
		} catch (IRCServerException $e) {
			echo $e->getMessage() . "\n";
		}
		
		echo "Connection has been closed. Reconnecting in 10 seconds\n";
		sleep(10);
	}
}