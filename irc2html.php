<?php
class IRC2HTML {
	/**
	*	IRC 2 HTML Class
	*	
	*	@author		Dale Horton
	*	@date		05 Mar 2009
	*
	*
	*	@method		parseStr($body, [$flags);
	*
	*	@param		$body	String
	*				Content to parse to html
	*
	*	@param		$flags	Integer
	*				Optional flags to hide certain message types
	*				HIDE_NONE, HIDE_JOINS, HIDE_NICKCHANGE, HIDE_MODES, HIDE_ERRORS, HIDE_ALL
	*
	*
	*	@method		parseFile($body, [$flags);
	*
	*	@param		$path		String
	*				Path to your base log directory
	*
	*	@param		$maxlines	Integer
	*				Total lines to parse from the log (starting from the end)
	*
	*	@param		$network	String
	*				Folder to search for logs (typically the network name like Gamesurge)
	*
	*	@param		$channel	String
	*				Name of the file to parse (without the .log extension)
	*
	*	@param		$flags		Integer
	*				Optional flags to hide certain message types
	*				HIDE_NONE, HIDE_JOINS, HIDE_NICKCHANGE, HIDE_MODES, HIDE_ERRORS, HIDE_ALL
	*
	**/
	
	private static $color = array(
		'#FFFFFF',
		'#000000',
		'#232C40',
		'#121620',
		'#465880',
		'#886D9F',
		'#6983BF',
		'#7B99DF',
		'#8CAFFF',
		'#D7DEF0',
		'#4B6EC0',
		'#6E84B5',
		'#FD0702',
		'#FFFFE0',
		'#7F7F7F',
		'#000000'
	);

	const HIDE_NONE = 0;
	const HIDE_JOINS = 1;
	const HIDE_NICKCHANGE = 2;
	const HIDE_MODES = 4;
	const HIDE_ERRORS = 8;
	const HIDE_ALL = 15;
	
	private static function translatecolorcode($matches) {
		$options = '';
		
		if($matches[2] != '') {
			$bgcolor = trim(substr($matches[2],1));
			$options .= 'background-color: ' . self::$color[(int)$bgcolor] . '; ';
		}
		
		$forecolor = trim($matches[1]);
		if($forecolor != '') {
			$options .= 'color: ' . self::$color[(int)$forecolor] . ';';
		}
		
		if($options != '') {
			return '<span style="' . $options . '">' . $matches[3] . '</span>';
		} else {
			return $matches[3];
		}
	}
	
	public static function parseStr($body, $flags=self::HIDE_ALL) {
		$lines = explode("\n", $body);
		$out = '';
		
		foreach($lines as $line) {
			if($flags & self::HIDE_ERRORS && (strpos($line,'12 Error:') !== FALSE)) continue;
			if($flags & self::HIDE_JOINS && (strpos($line,' has 07left IRC') !== FALSE || strpos($line,' has 08joined') !== FALSE || strpos($line,' has left') !== FALSE)) continue;
			if($flags & self::HIDE_NICKCHANGE && (strpos($line,'is known as09') !== FALSE)) continue;
			if($flags & self::HIDE_MODES && (strpos($line,'10sets mode:08') !== FALSE)) continue;

			$line = htmlentities($line);
			$line = preg_replace('/[\002]([^\002\x0F]*)(?:[\002])?/','<strong>$1</strong>',$line);
			$line = preg_replace('/[\x1F]([^\x1F\x0F]*)(?:[\x1F])?/','<u>$1</u>',$line);
			$line = preg_replace_callback('/[\003](\d{0,2})(,\d{1,2})?([^\003\x0F]*)(?:[\003](?!\d))?/',array('self','translatecolorcode'),$line);
			$line = preg_replace('/[\002\003\x1F\x0F]/','',$line);
			$line = preg_replace('/(http:\/\/.+?(?:\s|\z))/i','<a href="$1">$1</a>',$line);
			if($line != '') 
				$out .= $line . '<br>' . "\n";
		}
		
		return $out;
	}
	
	public static function parseFile($path, $maxlines=1000, $network='', $channel='', $flags=self::HIDE_ALL) {
		if($network != '') {
			$network = addslashes($network);
			$channel = addslashes($channel);
			if($channel[0] != '#') $channel = '#' . $channel;
			$filename = $path . $network . '/' . $channel . '.log';
		} else {
			$filename = $path;
		}
		
		$chunksize = 800;
		$fp = fopen($filename,'r');
		$i=0;
		$ptr = $size = filesize($filename);
		while($ptr > 0 && $i < $maxlines) {
			$i++;
			fseek($fp, $ptr-$chunksize);
			$chunk = fread($fp, $chunksize);
			if(preg_match('/.*\n(.+?)$/',$chunk,$matches)) {
				$ptr -= strlen($matches[1]) + 1;
				echo self::parseStr($matches[1],$flags);
				flush();
			}
		}
		fclose($fp);
	}
}
?>