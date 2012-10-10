<?php

$email = 'viper7@viper-7.com';
Validate::email($email, $errno, $errmsg);
echo $errmsg . "\n";



class Validate {
	// Email Validation Class - Requires PHP 5.2 or greater.
	// 
	// * ----------------------------------------------------------------------------
	// * "THE BEER-WARE LICENSE" (Revision 42):
	// * <viper7@viper-7.com> wrote this file. As long as you retain this notice you
	// * can do whatever you want with this stuff. If we meet some day, and you think
	// * this stuff is worth it, you can buy me a beer in return.   Dale Horton
	// * ----------------------------------------------------------------------------
	//
	// bool 	Validate::email($email_address, [&$error_number], [&$error_message])
	// string	Validate::errno_to_str($error_number)

	const EMAIL_VALID = 0;
	const EMAIL_SYNTAX_ERROR = 1;
	const EMAIL_DNS_ERROR = 2;
	const EMAIL_SERVER_ERROR = 3;
	const EMAIL_INVALID_USER = 4;

	public function email($email, &$errno = NULL, &$errmsg = NULL) {
		switch(FALSE) {
			case self::email_parse_syntax($email):
				$errno = self::EMAIL_SYNTAX_ERROR;
				break;
			case $hosts = self::email_check_mx($email):
				$errno = self::EMAIL_DNS_ERROR;
				break;
			case $smtp = self::email_check_smtp($email, $hosts):
				$errno = self::EMAIL_SERVER_ERROR;
				break;
			case $smtp !== 2:
				$errno = self::EMAIL_INVALID_USER;
				break;
			default:
				$errno = self::EMAIL_VALID;
		}
		$errmsg = self::errno_to_str($errno);
		if($errno === self::EMAIL_VALID) return TRUE; else return FALSE;
	}

	public function errno_to_str($errno) {
		switch($errno) {
			case self::EMAIL_VALID:
				return 'Address is valid';
			case self::EMAIL_SYNTAX_ERROR:
				return 'Syntax error in address';
			case self::EMAIL_DNS_ERROR:
				return 'Domain does not exist or no MX record';
			case self::EMAIL_SERVER_ERROR:
				return 'No operational mail servers found to handle this address';
			case self::EMAIL_INVALID_USER:
				return 'Domain server will not accept mail for this address';
		}
	}
	
	private function email_parse_syntax($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	
	private function email_check_mx($email) {
		$host = self::email_get_host($email);
		$result = getmxrr($host, $mxhosts);
		return $mxhosts;
	}

	private function email_get_host($email) {
		$host = trim(strrchr($email, '@'), '@');
		return $host;
	}
	
	private function email_get_user($email) {
		$user = substr($email, 0, strpos($email, '@')-1);
		return $user;
	}

	private function email_check_smtp($email, $mxhosts) {
		$ack = FALSE;
		$validuser = NULL;

		foreach($mxhosts as $key => $mxhost) {
			$nextserver = FALSE;
			$sock = @fsockopen($mxhost, 25, $errno, $errmsg, 10);
			if($sock !== FALSE) { 
				$response = fgets($sock);
				if(strpos($response,'220') === FALSE) continue; // wait for a welcome message

				fwrite($sock, 'HELO ' . $mxhost . "\r\n");

				$response = fgets($sock);
				if(strpos($response,'250') !== FALSE) { // wait for a response
					$ack = TRUE;
				}
				
				fwrite($sock, 'VRFY ' . $email . "\r\n");
				$response = fgets($sock);
				$response = substr($response, 0, strpos($response, ' '));
				switch($response) {
					case '250':	// user is valid
					case '251':	// valid forwarder address
					case '252':	// unable to validate but server will accept mail for this address
						$validuser = TRUE;
						break;
					case '551':	// user not local, no forwarder available
						$nextserver = TRUE;
						break;
					case '550':	// mailbox does not exist
					case '553':	// mailbox name not allowed
						$validuser = FALSE;
						break;
				}

				fwrite($sock, "QUIT \r\n");
				fclose($sock);
			}

			if($ack) {
				if($validuser === FALSE) return 2;
				if(!$nextserver) return 1;
			}
		}
		return FALSE;
	}
}
