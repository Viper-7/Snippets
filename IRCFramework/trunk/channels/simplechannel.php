<?php
/**
* This example class would join the channel #simplechannel and provide a few basic triggers
*
* @package V7IRCFramework
*/
class simplechannel extends IRCServerChannel
{
	public function event_msg($who, $message)
	{
		$message_parts = explode(' ', $message);
		switch($message_parts[0])
		{
			case '!time':
				$this->send_msg('The current date & time is ' . date('r'));
				break;
			case '!spin':
				$user = $this->users[array_rand($this->users)];
				$this->send_msg("{$user->nick} wins!");
				break;
		}
	}
}