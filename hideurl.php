<?php
class HideURL {
	private $iv = '68724868';
	private $key = 'THISISASECUREENCRYPTIONKEY!!&@&#';
	private $mcrypt;
	
	public function __construct() {
		$this->mcrypt = extension_loaded('mcrypt');
	}
	
	public function create($vars) {
		$url = http_build_query($vars);
		
		if($this->mcrypt) {
			$url = mcrypt_encrypt(MCRYPT_BLOWFISH, $this->key, $url, MCRYPT_MODE_CBC, $this->iv);
			$url = urlencode(base64_encode($url));
		} else {
			$url = urlencode(str_rot13(base64_encode($url)));
		}
		
		return $url;
	}
	
	public function read($var) {
		if($this->mcrypt) {
			$var = base64_decode($var);
			$var = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $this->key, $var, MCRYPT_MODE_CBC, $this->iv));
		} else {
			$var = trim(base64_decode(str_rot13($var)));
		}
		
		parse_str($var, $vars);
		return $vars;
	}
}

$hideurl = new HideURL();
$args = array('customerid' => 1234, 'email' => 'viper7@viper-7.com');
$url = 'http://example.com/unsub.php?x=' . $hideurl->create($args);


// unsub.php

if(isset($_GET['x'])) {
	$hideurl = new HideURL();
	$args = $hideurl->read($_GET['x']);
	echo $args['customerid'] . ' - ' . $args['email'];
}

