<?php
	if(Config::get('profiler_enable')) 
	{
		echo '<br/><br/><br/><div class="debug header"><strong>Debug Output</strong></div><br/>';
		echo '<fieldset><legend>$_SERVER</legend><pre style="margin: 0px">' . htmlentities(print_r($_SERVER, TRUE), ENT_QUOTES) . '</pre></fieldset>';
		echo '<fieldset><legend>$_GET</legend><pre style="margin: 0px">' . htmlentities(print_r($_GET, TRUE), ENT_QUOTES) . '</pre></fieldset>';
		echo '<fieldset><legend>$_POST</legend><pre style="margin: 0px">' . htmlentities(print_r($_POST, TRUE), ENT_QUOTES) . '</pre></fieldset>';
		echo '<fieldset><legend>Controller</legend><pre style="margin: 0px">' . htmlentities(print_r($controller, TRUE), ENT_QUOTES) . '</pre></fieldset>';

		echo '<br/><br/>Page generated In ' . number_format(microtime(TRUE) - $GLOBALS['start'], 5) . 's';
	}
?>