<!doctype html>
 <html><head>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script type="text/javascript" src="https://raw.github.com/kemayo/maphilight/master/jquery.maphilight.js"></script>
<script type="text/javascript">
    $(function () {
        $('.map').maphilight({ stroke: false, fillOpacity: 0.3 });
    });
</script>
</head>
<body>
<?php
$map = new PolyDetect('http://img.photobucket.com/albums/v227/ssjvegeta/RE5_Mysterious_Woman_BACK.png');
echo $map->getImgMap('javascript:alert(\'Hi!\');');


class PolyDetect {
	protected $poly_found = array();
	protected $gd, $path;

	function __construct($path) {
		$this->path = $path;
		
		$path = tempnam(sys_get_temp_dir(), '');
		if(!copy($this->path, $path)) {
			throw new RuntimeException('Failed to access image');
		}
		
		if(!file_exists($path) || !is_readable($path))
			throw new RuntimeException('File not found');
		
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type = finfo_file($finfo, $path);
		finfo_close($finfo);
		
		switch($type) {
			case 'image/png':
				$gd = imagecreatefrompng($path);
				break;
			case 'image/jpeg':
				$gd = imagecreatefromjpeg($path);
				break;
			case 'image/gif':
				$gd = imagecreatefromgif($path);
				break;
			default:
				throw new RuntimeException('Invalid file format');
		}
		
		unlink($path);
		$this->gd = $gd;
	}
	
	function getImgMap($link, $name = null) {
		if(!$name) $name = 'map' . md5(uniqid('', true));
		
		return $this->getImgTag($name) . $this->getMapTag($link, $name);
	}
	
	function getImgTag($map_name) {
		return "<img src=\"{$this->path}\" usemap=\"#{$map_name}\" class=\"map\"/>";
	}
	
	function getMapTag($link, $map_name) {
		$name = ltrim($map_name, '#');
		
		$html = "<map name=\"{$name}\">";
		$html .= "<area shape=\"poly\" coords=\"{$this->getPoly()}\" href=\"{$link}\" />";
		$html .= "</map>";
		
		return $html;
	}
	
	function getPoly() {
		$this->poly_found = array();

		$ret = $this->getFirstNonAlphaPixel();

		while($ret) {
			list($x, $y) = $ret;
			$poly[] = "$x,$y";

			$ret = $this->getNextAlphaPixel($x, $y);
		}
		
		return implode(',', $poly);
	}
		
	function getNextAlphaPixel($x, $y) {
		static $last_i;
		
		$mask = array(
			// Top
			array(-1,-1), //0
			array(0,-1), //1
			array(1,-1), //2

			// Mid Left
			array(-1,0), //3

			// Mid Right
			array(1,0), //4

			// Bottom
			array(-1,1), //5
			array(0,1), //6
			array(1,1), //7
		);

		$xl = imagesx($this->gd);
		$yl = imagesy($this->gd);
		
		foreach($mask as $i => $m) {
			$x2 = $x + $m[0];
			$y2 = $y + $m[1];
			
			if($x2 >= $xl || $x2 < 0 || $y2 >= $yl || $y2 < 0) continue;
				
			if(!$this->getPixelAlphaBoolean($x2, $y2) && !isset($this->poly_found[$x2][$y2])) {
				
				foreach($mask as $c_m) {
					$x3 = $x2 + $c_m[0];
					$y3 = $y2 + $c_m[1];
					
					if($x3 >= $xl || $x3 < 0 || $y3 >= $yl || $y3 < 0) continue;
					
					if($this->getPixelAlphaBoolean($x3, $y3)) {
						$this->poly_found[$x2][$y2] = true;

						if($i == $last_i)
							return $this->getNextAlphaPixel($x2, $y2);

						$last_i = $i;
						return array($x2, $y2);
					}
				}
			}
		}
		
		return FALSE;
	}

	function getFirstNonAlphaPixel() {
		foreach(range(0, imagesx($this->gd)-1) as $x) {
			foreach(range(0, imagesy($this->gd)-1) as $y) {
				if(!$this->getPixelAlphaBoolean($x, $y))
					return array($x, $y);
			}
		}
	}

	function getPixelRGBA($x, $y) {
		return imagecolorsforindex($this->gd, imagecolorat($this->gd, $x, $y));
	}
	
	function getPixelAlphaBoolean($x, $y) {
		$rgba = $this->getPixelRGBA($x, $y);
		return $rgba['alpha'] > 120;
	}
}
?>
</body>
</html>