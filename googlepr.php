<?php
$obj = new GooglePR();
$pr = $obj->getPR('http://www.overclockers.com.au');
echo $pr;

Class GooglePR {

	public $googleDomains = Array(
		"toolbarqueries.google.com",
		"www.google.com");
	
	public $userAgent = "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1) Gecko/20021204";

	function getPR($url) {
		$result = array();
		$contents="";

		if (($url.""!="")&&($url.""!="http://")) {

			$this->cacheDir .= (substr($this->cacheDir,-1) != "/")? "/":"";

			$url_ = (substr(strtolower($url),0,7) != "http://") ? "http://".$url : $url;
			$host = $this->googleDomains[mt_rand(0,count($this->googleDomains)-1)];
			$url = sprintf("http://%s/search?client=navclient-auto&ch=%s&features=Rank&q=%s", $host, $this->CheckHash($this->HashURL($url_)), urlencode("info:".$url_));

			$context_options = array();
			$context_options['http']['user_agent'] = $this->userAgent;
			$context = stream_context_create($context_options);
			
			$contents = trim(file_get_contents($url, FALSE, $context));
			$result['Response'] = $contents;
			
			// Rank_1:1:0 = 0
			// Rank_1:1:5 = 5
			// Rank_1:1:9 = 9
			// Rank_1:2:10 = 10 etc
			$p=explode(":",$contents);
			if (isset($p[2])) $result['Pagerank']=$p[2];
		}

		if(!isset($result['Pagerank']) || $result['Pagerank'] == -1) $result['Pagerank'] = 0;
		
		return $result['Pagerank'];
	}

	//convert a string to a 32-bit integer
	function StrToNum($Str, $Check, $Magic) {
		$Int32Unit = 4294967296;  // 2^32
		$length = strlen($Str);
		for ($i = 0; $i < $length; $i++) {
			$Check *= $Magic; 	
			//If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31), 
			//  the result of converting to integer is undefined
			//  refer to http://www.php.net/manual/en/language.types.integer.php
			if ($Check >= $Int32Unit) {
				$Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
				//if the check less than -2^31
				$Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
			}
			$Check += ord($Str{$i}); 
		}
		return $Check;
	}

	//genearate a hash for a url
	function HashURL($String) {
		$Check1 = $this->StrToNum($String, 0x1505, 0x21);
		$Check2 = $this->StrToNum($String, 0, 0x1003F);
		$Check1 >>= 2; 	
		$Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
		$Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
		$Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);	
		
		$T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
		$T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );
		
		return ($T1 | $T2);
	}
	
	//genearate a checksum for the hash string
	function CheckHash($Hashnum) {
		$CheckByte = 0;
		$Flag = 0;
		$HashStr = sprintf('%u', $Hashnum) ;
		$length = strlen($HashStr);
		
		for ($i = $length - 1;  $i >= 0;  $i --) {
			$Re = $HashStr{$i};
			if (1 === ($Flag % 2)) {			  
				$Re += $Re;	 
				$Re = (int)($Re / 10) + ($Re % 10);
			}
			$CheckByte += $Re;
			$Flag ++;	
		}
	
		$CheckByte %= 10;
		if (0 !== $CheckByte) {
			$CheckByte = 10 - $CheckByte;
			if (1 === ($Flag % 2) ) {
				if (1 === ($CheckByte % 2)) {
					$CheckByte += 9;
				}
				$CheckByte >>= 1;
			}
		}
		return '7'.$CheckByte.$HashStr;
	}
}

?>
