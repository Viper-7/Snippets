<?php
	/* SimpleIRC - Non-persistent IRC Scraper
	 * ----------------------------------------------------------------------------
	 * "THE BEER-WARE LICENSE" (Revision 42):
	 * <viper7@viper-7.com> wrote this file. As long as you retain this notice you
	 * can do whatever you want with this stuff. If we meet some day, and you think
	 * this stuff is worth it, you can buy me a beer in return.   Dale Horton
	 * ----------------------------------------------------------------------------
	 */

	
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//
	// connect($server, [$nick], [$port], [$host])          Connects to $server:$port with nickname $nick
	//                                                      $host must be set to the local hostname for some servers
	//                                                      Returns a pointer which can be used when connecting to multiple servers (optional)
	//
	// disconnect([$pointer])                               Disconnects from server $pointer, or from all servers if no pointer supplied
	//
	// getnamelist($channel, [$pointer])                    Gets a list of users in $channel. Channel must not be set +s (secret)
	//
	// getuserlist($channel, [$pointer])                    Gets a detailed list of users in $channel. Channel must not be set +s (secret)
	//
	// getchanlist([$channel], [$pointer])                  Gets info about $channel, or all channels if none supplied
	//
	// gettopic($channel, [$pointer])                       Gets the current topic of $channel
	//
	// gettopicdetail($channel, [$pointer])                 Gets the current topic of $channel, with extra details (who set it, and when)
	//
	// whois($user, [$pointer])                             Gets details about $user
	//
	// send($destination, $message, [$pointer])             Sends $message to User or Channel $destination
	//                                                      Channels must not be set +n (no external messages)
	//
	// sendctcp($destination, $message, [$pointer])         Sends a CTCP $message to user $destination (Channels are not supported)
	//                                                      NOTE: This function will block waiting for a reply! 
	//                                                            If you send an invalid CTCP request, the script may hang and timeout.
	//
	//--------------------------------------------------------------------------------------------------------------------------------------------

	
	 class simpleirc {
		private $connections;
		
		function __construct() {
			$this->connections = array();
		}
		
		function connect($server, $nick='testbot', $port = '6667', $host = 'localhost') {
			$tag = preg_replace('/[^a-z]/i','',$server.$nick);

			$fp = fsockopen($server,$port);
			if($fp) {
				fputs($fp, "NICK $nick\r\n");
				fputs($fp, "USER $nick $host $server $nick\r\n");
				
				while(!feof($fp)) { // Wait for connection
					$line = fgets($fp); // Fetch a line from the server
					if(substr($line,0,4) == 'PING') {
						$out = 'PONG :' . substr($line,6);
						fputs($fp, $out);
					}
					if(substr($line,0,7) == 'VERSION') {
						$out = 'VERSION :SimpleIRC 0.03';
						fputs($fp, $out);
					}
					if(strpos($line,'Closing Link') || strpos($line,' 433 ')) { // Don't waste time processing if the request failed
						fclose($fp);
						return FALSE;
					} 
					
					if(strpos($line,' 376 ')) break; // Wait until the end of the server's MOTD response
				}
				
				$this->connections[$tag] = $fp;
				$this->whois($nick);
				return $tag;
			} else {
				return FALSE;
			}
		}
		
		function disconnect($tag = NULL, $quitmsg = 'bye') {
			foreach($this->connections as $key => $pointer) {
				if($tag === NULL || $tag == $key) {
					fputs($this->connections[$key], "QUIT $quitmsg\r\n");
					fclose($this->connections[$key]);
					unset($this->connections[$key]);
				}
			}
		}

		function getuserlist($channel, $tag = NULL) {
			if($tag === NULL) $tag = key($this->connections);

			fputs($this->connections[$tag], "WHO $channel\r\n");

			while(!feof($this->connections[$tag])) { // Process output
				$line = fgets($this->connections[$tag]); // Fetch a line from the server

				if(strpos($line,' 352 ')) { // process each WHO response
					$arr=explode(' ',$line);

					$user['nick'] = $arr[7];
					$user['ident'] = trim($arr[4][1] == '=' ? substr($arr[4],2) : $arr[4]);
					$user['host'] = $arr[5];
					$user['server'] = $arr[6];
					$user['fullname'] = trim(substr($line,strpos($line,':',2)+3));

					$user['status'] = 'normal';
					if(strpos($arr[8],'+')) $user['status'] = 'voiced';
					if(strpos($arr[8],'%')) $user['status'] = 'halfop';
					if(strpos($arr[8],'@')) $user['status'] = 'op';
					
					$out[] = $user;
				}

				if(strpos($line,' 315 ')) break; // wait for "End of /WHO" response
			}
			
			return $out;
		}

		function getnamelist($channel, $tag = NULL) {
			if($tag === NULL) $tag = key($this->connections);

			fputs($this->connections[$tag], "NAMES $channel\r\n");

			while(!feof($this->connections[$tag])) { // Process output
				$line = fgets($this->connections[$tag]); // Fetch a line from the server

				if(strpos($line,' 353 ')) { // process each NAMES response
					$names = explode(' ',trim(substr($line,strpos($line,':',2)+1)));
					foreach($names as $name) {
						$out[] = $name;
					}
				}

				if(strpos($line,' 366 ')) break; // wait for "End of /WHO" response
			}
			
			return $out;
		}

		function gettopic($channel, $tag = NULL) {
			if($tag === NULL) $tag = key($this->connections);

			fputs($this->connections[$tag], "TOPIC $channel\r\n");

			while(!feof($this->connections[$tag])) { // Process output
				$line = fgets($this->connections[$tag]); // Fetch a line from the server

				if(strpos($line,' 332 ')) { // process each TOPIC response
					$topic = trim(substr($line,strpos($line,':',2)+1));
				}

				if(strpos($line,' 333 ')) break; // wait for "End of /TOPIC" response
				
				if(strpos($line,' 403 ')) {
					return '';
				}
			}
			
			return $topic;
		}

		function gettopicdetail($channel, $tag = NULL) {
			if($tag === NULL) $tag = key($this->connections);

			fputs($this->connections[$tag], "TOPIC $channel\r\n");

			while(!feof($this->connections[$tag])) { // Process output
				$line = fgets($this->connections[$tag]); // Fetch a line from the server

				if(strpos($line,' 332 ')) { // process each TOPIC response
					$topic['topic'] = trim(substr($line,strpos($line,':',2)+1));
				}

				if(strpos($line,' 333 ')) {
					$arr = explode(' ', trim($line));

					$topic['channel'] = $arr[3];
					$topic['setby'] = $arr[4];
					$topic['timestamp'] = $arr[5];

					break;
				}
				
				if(strpos($line,' 403 ')) {
					return FALSE;
				}
			}
			
			return $topic;
		}
		
		function getchanlist($channel = '', $tag = NULL) {
			if($tag === NULL) $tag = key($this->connections);
			
			$getfull = false;
			
			fputs($this->connections[$tag], "LIST $channel\r\n");

			if($channel == '') {
				$getfull = true;
			} else {
				while(!feof($this->connections[$tag])) { // Process output
					$line = fgets($this->connections[$tag]); // Fetch a line from the server

					if(strpos($line,' 322 ')) { // process each LIST response
						$arr = explode(' ', trim($line));
						
						$chan['name'] = $arr[3];
						$chan['users'] = $arr[4];
						$chan['topic'] = trim(substr($line,strpos($line,':',2)+1));
						
						$out = $chan;
					}

					if(strpos($line,' 323 ')) break; // wait for "End of /LIST" response
					if(strpos($line,' 263 ')) {
						$getfull = true;
						break;
					}
				}
			}
			
			if($getfull) {
				fputs($this->connections[$tag], "LIST\r\n");

				while(!feof($this->connections[$tag])) { // Process output
					$line = fgets($this->connections[$tag]); // Fetch a line from the server

					if(strpos($line,' 322 ')) { // process each LIST response
						$arr = explode(' ', trim($line));
						
						$chan['name'] = $arr[3];
						$chan['users'] = $arr[4];
						$chan['topic'] = trim(substr($line,strpos($line,':',2)+1));
						
						if(strlen($channel) > 0) {
							if(strtolower(trim($channel)) == strtolower(trim($name))) {
								$out = $chan;
							}
						} else {
							$out[] = $chan;
						}
					}

					if(strpos($line,' 323 ')) break; // wait for "End of /LIST" response
					if(strpos($line,' 263 ')) {
						return FALSE;
					}
				}
			}
			return $out;
		}

		function whois($user, $tag = NULL) {
			if($tag === NULL) $tag = key($this->connections);

			$lines = array();
			
			fputs($this->connections[$tag], "WHOIS $user\r\n");
			
			while(!feof($this->connections[$tag])) { // Process output
				$line = fgets($this->connections[$tag]); // Fetch a line from the server

				if(strpos($line,' 318 ')) break; // wait for "End of /WHO" response
				if(strpos($line,' 401 ')) return FALSE;
				
				$arr = explode(' ', $line);

				if(strpos($line,' 311 ')) {
					$out['nick'] = $user;
					$out['ident']= $arr[4][1] == '=' ? substr($arr[4],2) : $arr[4];
					$out['host']= $arr[5];
					$out['fullname']= trim(substr($line,strpos($line,':',2)+1));
				}
					
				if(strpos($line,' 319 ')) {
					$out['channels']= explode(' ',trim(substr($line,strpos($line,':',2)+1)));
				}

				if(strpos($line,' 312 ')) {
					$out['serverhost']= $arr[4];
					$out['serveraddress']= trim(substr($line,strpos($line,':',2)+1));
				}
			}

			return $out;
		}

		function send($destination, $message, $tag = NULL) {
			if($tag === NULL) $tag = key($this->connections);

			$message = explode("\n",$message);

			foreach($message as $line) {
				$line = trim($line);
				fputs($this->connections[$tag], "PRIVMSG $destination :$line\r\n");
			}
		}

		function sendctcp($destination, $message, $tag = NULL) {
			if($tag === NULL) $tag = key($this->connections);
			
			if($this->whois($destination)) {
				$message = explode("\n",$message);
				
				foreach($message as $line) {
					$line = trim($line);
					fputs($this->connections[$tag], "PRIVMSG $destination :\001{$line}\001\r\n");
				}
				
				while(!feof($this->connections[$tag])) {
					$line = fgets($this->connections[$tag]); // Fetch a line from the server
					if(strpos($line,' NOTICE ')) {
						$out = substr(trim(substr($line,strpos($line,"\001",2)+1)),0,-1);
						$out = preg_replace('/\003\d+(?:,\d+)?|\003|\002|' . chr(15) . '|\031/','',$out);
						return $out . "\r\n";
					}
				}
			}
		}
	}
?>
