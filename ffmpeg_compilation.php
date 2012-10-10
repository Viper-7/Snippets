<?php
/*
Background Image *
Ken Burns zoom/pan on background
	Auto random values
Text Overlay *
	Choose position/font/size/color
Text Background Bar
	Choose color/alpha
Text Animation
	Constant Motion
	Entry / Exit
	Acceleration / Deceleration
	Motion Blur
	Templates *
		Slide in from Left & Right
		Slide in from Left & Bottom
		Slide in from Top & Bottom
Audio mixing *
	Crossfade?
*/
interface FFMpeg_VideoObject {}
interface FFMpeg_AudioObject {}
class FFMpeg_Object {}

class FFMpeg_Executable {
	protected static $pipes = array();
	
	public function run() {
		foreach(self::$pipes as $frame => $pipe) {
			echo '<pre>';
			var_dump('ffmpeg ' . implode(' ', $pipe));
			echo '</pre>';
		}
	}
	
	public function registerPipeSource($frame, $vpipe, $apipe = null) {
		self::$pipes[$frame] = array('v' => $vpipe, 'a' => $apipe);
	}
}

class FFMpeg_Compilation extends FFMpeg_Object implements FFMpeg_VideoObject {
	protected $width = 320;
	protected $height = 240;
	
	protected $vbitrate = 700000;
	protected $abitrate = 128000;
	protected $frame_rate = 25;
	
	protected $length = null;
	protected $frame = 0;
	protected $work_queue = array();
	
	public function seek($frame, $absolute = true) {
		if($absolute) {
			$this->frame = $frame;
		} else {
			$this->frame += $frame;
		}
	}
	
	public function addContent($video, $audio = null, $transition = null, $length = null) {
		if(!$audio) $audio = new FFMpeg_Audio();
		if(!$transition) $transition = new FFMpeg_Transition('none');
		
		if(!is_a($video, 'FFMpeg_VideoObject')) {
			throw new Exception('Video added to an FFMpeg Compilation must be an video clip, or image.');
		}
		
		if(!is_a($audio, 'FFMpeg_AudioObject')) {
			throw new Exception('Audio added to an FFMpeg Compilation must be an audio file.');
		}
		
		if(!is_a($transition, 'FFMpeg_Transition')) {
			if(is_string($transition))
				$transition = new FFMpeg_Transition($transition);
			else
				throw new Exception('Transitions added to an FFMpeg Compilation must extend FFMpeg_Transition.');
		}

		if(!$length)
			$length = min($video->getLength($this->frame_rate), $audio->getLength($this->frame_rate));

		$this->work_queue[$this->frame] = array(
			'video' => $video,
			'audio' => $audio,
			'transition' => $transition,
			'length' => $length,
		);
		
		$this->seek($length, false);
	}
	
	public function render() {
		$frame = 0;
		$process_pipe = array();
		$exec = new FFMpeg_Executable();

		while(isset($this->work_queue[$frame])) {
			extract($this->work_queue[$frame]);
			
			$length = min($video->getLength($this->frame_rate), $audio->getLength($this->frame_rate));
			
			if(isset($this->work_queue[$frame + $length])) {
				$nextvideo = $this->work_queue[$frame + $length]['video'];
				$nextaudio = $this->work_queue[$frame + $length]['audio'];
				
				$tlen = $transition->getLength();
				$tstart = $frame + $length - ($tlen / 2);
				
				$tvsrc = $transition->getVideoPipeSource($video->getLastFrame(), $nextvideo->getFirstFrame(), $this->frame_rate);
				$tasrc = $transition->getAudioPipeSource($audio, $nextaudio, $this->frame_rate);
			} else {
				$tvsrc = null;
				$tasrc = null;
			}
			
			$vsrc = $video->getPipeSource($this->frame_rate);
			$asrc = $audio->getPipeSource($this->frame_rate);
			
			if($tvsrc) {
				$exec->registerPipeSource($frame, $vsrc, $asrc);
				$exec->registerPipeSource($tstart, $tvsrc, $tasrc);
			} else {
				$exec->registerPipeSource($frame, $vsrc, $asrc);
			}
			
			$frame += $length;
		}
		
		$exec->run();
	}
}

class FFMpeg_Video extends FFMpeg_Object implements FFMpeg_VideoObject {
	protected $source_path = null;
	protected $frame_rate = 25;
	protected $length = 0;
	protected $start = 0;
	protected $text_overlays = array();
	
	public function getLength($frame_rate) {
		return $this->length;
	}
	
	public function getFirstFrame() {
		//-itsoffset 0 -vframes 1 -f rawvideo -vcodec png
	}

	public function getLastFrame() {
		//-itsoffset {$this->length} -vframes 1 -f rawvideo -vcodec png
	}
	
	public function getPipeSource() {
		return "-video pipe [\"{$this->source_path}\", $this->length]";
	}

	public function overlay($text) {
		if(!is_a($text, 'FFMpeg_Text')) {
			if(is_string($text)) 
				$text = new FFMpeg_Text($text);
			else
				throw new Exception('Only Text objects can be overlayed onto an Image');
		}
		
		$this->text_overlays[] = $text;
	}
}

class FFMpeg_Image extends FFMpeg_Object implements FFMpeg_VideoObject {
	protected $display_time = 10;
	protected $source_path = null;
	protected $text_overlays = array();
	
	public function __construct($path) {
		$this->source_path = $path;
	}
	
	public function overlay($text) {
		if(!is_a($text, 'FFMpeg_Text')) {
			if(is_string($text)) 
				$text = new FFMpeg_Text($text);
			else
				throw new Exception('Only Text objects can be overlayed onto an Image');
		}
		
		$this->text_overlays[] = $text;
	}

	public function getLength($frame_rate) {
		return $this->display_time * $frame_rate;
	}

	public function getFirstFrame() {
		return "<img src=\"../media/{$this->source_path}\" width=20 height=20>";
		//-itsoffset 0 -vframes 1 -f rawvideo -vcodec png
	}

	public function getLastFrame() {
		return "<img src=\"../media/{$this->source_path}\" width=20 height=20>";
		//-itsoffset {$this->length} -vframes 1 -f rawvideo -vcodec png
	}
	
	public function getPipeSource($frame_rate) {
		$overlays = implode(', ', $this->text_overlays);
		return "-image pipe [<img src=\"../media/{$this->source_path}\" width=20 height=20>, $overlays, {$this->getLength($frame_rate)}]";
	}
}

class FFMpeg_Text extends FFMpeg_Object implements FFMpeg_VideoObject {
	protected $text = null;
	protected $size = 8;
	protected $font = null;
	protected $path = null;
	
	public function __construct($text, $path) {
		if(!is_a($path, 'FFMpeg_Path')) {
			if(is_string($path)) 
				$path = new FFMpeg_Path($path);
			else
				throw new Exception('FFMpeg Text objects must contain a path');
		}
		
		$this->path = $path;
		$this->text = $text;
	}
	
	public function __toString() {
		return "\"{$this->text}\" ({$this->path->template})";
	}
}

class FFMpeg_Audio extends FFMpeg_Object implements FFMpeg_AudioObject {
	public $crossfade = false;
	public $source_path = null;
	public $length = 0;
	public $start = 0;
	
	public function __construct($path, $start_frame, $length) {
		$this->source_path = $path;
		$this->start = $start_frame;
		$this->length = $length;
	}
	
	public function getLength($frame_rate) {
		return $this->length;
	}
	
	public function getPipeSource($frame_rate) {
		$start = $this->start / $frame_rate;
		$length = $this->length / $frame_rate;
		
		return "-audio pipe [\"{$this->source_path}\", {$start}, {$length}]";
	}
}

class FFMpeg_Transition extends FFMpeg_Object {
	protected $length = 50;
	protected $template = null;
	
	public function __construct($template) {
		$this->template = $template;
	}
	
	public function getLength() {
		return $this->length;
	}
	
	public function getVideoPipeSource($video1, $video2, $fps) {
		return "-transition video pipe [{$video1}, {$video2}, {$this->length}, {$this->template}]";
	}
	
	public function getAudioPipeSource($audio1, $audio2, $fps) {
		$a1start = ($audio1->start + $audio1->length) / $fps;
		$a2start = ($audio2->start - $this->length) / $fps;
		$length = $this->length / $fps;
		
		return "-transition audio pipe [{$a1start}, \"{$audio1->source_path}\", {$a2start}, \"{$audio2->source_path}\", {$length}]";
	}
}

class FFMpeg_Path extends FFMpeg_Object {
	public $randomize = true;
	
	protected $pan  = array(0,0, 0,0);
	protected $zoom = array(0,0, 0,0);
	public $template = null;
	
	public function __construct($template) {
		$this->template = $template;
		// Load Template into members
	}
}

$comp = new FFMpeg_Compilation();


$image = new FFMpeg_Image('metallica_fade_to_black.jpg');
$audio = new FFMpeg_Audio('metallica_fade_to_black.mp3', 500, 250);
$image->overlay(new FFMpeg_Text("Metallica\nFade To Black", 'slide_top'));
$comp->addContent($image, $audio, 'fade');


$image = new FFMpeg_Image('shadow_gallery_rain.jpg');
$audio = new FFMpeg_Audio('shadow_gallery_rain.mp3', 500, 250);
$image->overlay(new FFMpeg_Text("Shadow Gallery\nRain", 'slide_sides'));
$comp->addContent($image, $audio, 'fade');


$image = new FFMpeg_Image('linkin_park_crawling.jpg');
$audio = new FFMpeg_Audio('linkin_part_crawling.mp3', 500, 250);
$image->overlay(new FFMpeg_Text("Linkin Park\nCrawling", 'slide_left'));
$comp->addContent($image, $audio, 'none');



$exec = new FFMpeg_Executable();
$comp->render();
