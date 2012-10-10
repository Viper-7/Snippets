<?php
class Stats
{
	public static function logRequest()
	{
		$config = Config::getInstance();
		$config->queries->stats->log_request();
	}
}