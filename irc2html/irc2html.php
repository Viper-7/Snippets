<?php
class IRC2HTML {
	//private static $color = array('white','black','navy','green','red','maroon','purple','olive','yellow','lime','teal','aqua','blue','fuchsia','gray','silver');
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
	
	public static function parseStr($body) {
		$lines = explode("\n", $body);
		$out = '';
		
		foreach($lines as $line) {
			$line = htmlentities($line);
			$line = preg_replace('/[\002]([^\002\x0F]*)(?:[\002])?/','<strong>$1</strong>',$line);
			$line = preg_replace('/[\x1F]([^\x1F\x0F]*)(?:[\x1F])?/','<u>$1</u>',$line);
			$line = preg_replace_callback('/[\003](\d{0,2})(,\d{1,2})?([^\003\x0F]*)(?:[\003](?!\d))?/',array('self','translatecolorcode'),$line);
			$line = preg_replace('/[\002\003\x1F\x0F]/','',$line);
			if($line != '') 
				$out .= $line . '<br>' . "\n";
		}
		
		return $out;
	}
	
	public static function parseFile($path, $maxlines=1000, $network='', $channel='') {
		$chunksize = 400;

		if($network != '') {
			$network = addslashes($network);
			$channel = addslashes($channel);
			if($channel[0] != '#') $channel = '#' . $channel;
			$filename = $path . $network . '/' . $channel . '.log';
		} else {
			$filename = $path;
		}
		
		$fp = fopen($filename,'r');
		$i=0;
		$ptr = $size = filesize($filename);
		while($ptr > 0 && $i < $maxlines) {
			$i++;
			fseek($fp, $ptr-$chunksize);
			$chunk = fread($fp, $chunksize);
			if(preg_match('/.*\n(.+?)$/',$chunk,$matches)) {
				$ptr -= strlen($matches[1]) + 1;
				echo self::parseStr($matches[1]);
				flush();
			}
		}
		fclose($fp);
	}
}
?>