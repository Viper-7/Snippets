<?php
namespace V7F\Helpers;

class ErrorHandler extends Singleton {
	public function error($msg, $fatal = TRUE) {
		$config = Registry::getInstance();
		
		if($config->error_log) {
			$fp = fopen($config->error_log, 'a');
			fwrite($fp, date('r') . ' : ' . $msg . "\n");
			fclose($fp);
		}

		if($config->show_errors) {
			if($fatal) {
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">';
				echo '<body><p>Fatal Framework Error: ' . $msg . '</p></body></html>';
				die();
			} else {
				echo '<p>Framework Error: ' . $msg . '</p>';
			}
		} else {
			if(!empty($config->error_page)) {
				include($config->error_page);
			}
			if($fatal) die();
		}
	}
	
	public function error_handler($errno, $errmsg, $errfile, $errline, $errcontext) {
		$fatalerrors = array(1, 16, 64, 256);
		
		if(in_array($errno, $fatalerrors)) $fatal = TRUE; else $fatal = FALSE;
		
		$this->error('"' . $errmsg . '" on line ' . $errline . ' of file ' . $errfile, $fatal);
	}
}
